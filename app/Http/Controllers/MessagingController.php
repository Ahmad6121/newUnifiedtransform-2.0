<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MessagingController extends Controller
{
    public function index()
    {
        return view('messages.index');
    }

    // ======================
    // Conversations List
    // ======================
    public function conversations(Request $r)
    {
        $user = $r->user();

        $convs = Conversation::query()
            // ✅ Admin الحقيقي فقط يشوف كل المحادثات
            ->when(!$this->isRealAdmin($user), function ($q) use ($user) {
                $q->whereHas('participants', function ($qq) use ($user) {
                    $qq->where('user_id', $user->id);
                });
            })
            ->with(['lastMessage.sender:id,first_name,last_name', 'users:id,first_name,last_name,email,role'])
            ->orderByDesc('last_message_at')
            ->limit(50)
            ->get()
            ->map(function ($c) use ($user) {

                // unread: meaningful only if user is participant
                $lastReadAt = null;
                $meUser = $c->users->firstWhere('id', $user->id);

                if ($meUser && $meUser->pivot) {
                    $lastReadAt = $meUser->pivot->last_read_at;
                }

                $unread = 0;
                if ($meUser && $meUser->pivot) {
                    $unreadQuery = Message::where('conversation_id', $c->id)
                        ->where('sender_id', '!=', $user->id);

                    if ($lastReadAt) {
                        $unreadQuery->where('created_at', '>', $lastReadAt);
                    }

                    $unread = $unreadQuery->count();
                }

                $otherUsers = $c->users->where('id', '!=', $user->id)->values();
                $otherNames = $otherUsers->map(function ($u) {
                    $fn = trim((string)($u->first_name ?? ''));
                    $ln = trim((string)($u->last_name ?? ''));
                    $full = trim($fn . ' ' . $ln);
                    return $full !== '' ? $full : ($u->email ?? 'User');
                });

                $title = $c->subject ? $c->subject : ($otherNames->implode(', ') ?: 'Conversation');

                return [
                    'id' => $c->id,
                    'type' => $c->type,
                    'title' => $title,
                    'last_message' => $c->lastMessage ? ($c->lastMessage->body ?: '') : '',
                    'last_time' => $c->lastMessage && $c->lastMessage->created_at ? $c->lastMessage->created_at->toDateTimeString() : null,
                    'unread' => $unread,
                ];
            });

        return response()->json($convs);
    }

    // ======================
    // Messages of a Conversation
    // ======================
    public function messages(Request $r, $conversationId)
    {
        $user = $r->user();
        $this->ensureCanAccess($user, (int)$conversationId);

        $msgs = Message::where('conversation_id', $conversationId)
            ->with('sender:id,first_name,last_name,email')
            ->orderByDesc('id')
            ->limit(60)
            ->get()
            ->reverse()
            ->values()
            ->map(function ($m) {
                $sender = $m->sender;
                $name = '';
                if ($sender) {
                    $fn = trim((string)($sender->first_name ?? ''));
                    $ln = trim((string)($sender->last_name ?? ''));
                    $name = trim($fn . ' ' . $ln);
                    if ($name === '') $name = $sender->email ?? 'User';
                }

                return [
                    'id' => $m->id,
                    'sender_id' => $m->sender_id,
                    'sender_name' => $name ?: 'User',
                    'body' => $m->body,
                    'created_at' => $m->created_at ? $m->created_at->toDateTimeString() : null,
                ];
            });

        // mark as read (only if participant exists)
        ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', $user->id)
            ->update(['last_read_at' => now()]);

        return response()->json($msgs);
    }

    // ======================
    // Start 1-1 Conversation
    // ======================
    public function start(Request $r)
    {
        $user = $r->user();

        $r->validate([
            'recipient_id' => 'required|exists:users,id',
        ]);

        $recipient = User::findOrFail($r->recipient_id);

        if (!$this->canStartChat($user, $recipient)) {
            return response()->json(['message' => 'Not allowed'], 403);
        }

        $type = $this->inferType($user, $recipient);

        $existing = Conversation::whereHas('participants', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->whereHas('participants', function ($q) use ($recipient) {
                $q->where('user_id', $recipient->id);
            })
            ->where('type', $type)
            ->first();

        if ($existing) {
            return response()->json(['conversation_id' => $existing->id]);
        }

        $convId = DB::transaction(function () use ($user, $recipient, $type) {
            $conv = Conversation::create([
                'type' => $type,
                'created_by' => $user->id,
                'last_message_at' => null,
            ]);

            ConversationParticipant::insert([
                [
                    'conversation_id' => $conv->id,
                    'user_id' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'conversation_id' => $conv->id,
                    'user_id' => $recipient->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            return $conv->id;
        });

        return response()->json(['conversation_id' => $convId]);
    }

    // ======================
    // Send a Message
    // ======================
    public function send(Request $r, $conversationId)
    {
        $user = $r->user();
        $conversationId = (int)$conversationId;

        $this->ensureCanAccess($user, $conversationId);

        // ✅ يمنع الإرسال داخل محادثات قديمة "غلط" حسب قواعدك الجديدة
        $this->ensureCanSendInConversation($user, $conversationId);

        $r->validate([
            'body' => 'required|string|max:4000',
        ]);

        // ✅ Admin الحقيقي لو يبعت وهو مش Participant (مراقبة)، ينضم تلقائي
        if ($this->isRealAdmin($user)) {
            ConversationParticipant::firstOrCreate(
                ['conversation_id' => $conversationId, 'user_id' => $user->id],
                ['is_admin_observer' => true, 'last_read_at' => now()]
            );
        }

        $msg = Message::create([
            'conversation_id' => $conversationId,
            'sender_id' => $user->id,
            'body' => $r->body,
            'message_type' => 'text',
        ]);

        Conversation::where('id', $conversationId)->update(['last_message_at' => now()]);

        return response()->json([
            'id' => $msg->id,
            'sender_id' => $msg->sender_id,
            'body' => $msg->body,
            'created_at' => $msg->created_at ? $msg->created_at->toDateTimeString() : null,
        ]);
    }

    // ======================
    // Admin Join (Optional)
    // ======================
    public function adminJoin(Request $r, $conversationId)
    {
        $user = $r->user();
        if (!$this->isRealAdmin($user)) return response()->json(['message' => 'Forbidden'], 403);

        ConversationParticipant::firstOrCreate(
            ['conversation_id' => $conversationId, 'user_id' => $user->id],
            ['is_admin_observer' => true, 'last_read_at' => now()]
        );

        return response()->json(['ok' => true]);
    }

    // ======================
    // User Search for new chats
    // ======================
    public function userSearch(Request $r)
    {
        $me = $r->user();
        $q = trim((string)$r->input('q', ''));

        $users = User::query()
            ->where('id', '!=', $me->id)
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($s) use ($q) {
                    $s->where('first_name', 'like', "%$q%")
                        ->orWhere('last_name', 'like', "%$q%")
                        ->orWhere('email', 'like', "%$q%");
                });
            })
            ->limit(50)
            ->get(['id','first_name','last_name','email','role']);

        // ✅ فلترة حسب قواعد السماح
        $users = $users->filter(function ($u) use ($me) {
            return $this->canStartChat($me, $u);
        })->values()->map(function ($u) {
            $fn = trim((string)($u->first_name ?? ''));
            $ln = trim((string)($u->last_name ?? ''));
            $name = trim($fn . ' ' . $ln);
            if ($name === '') $name = $u->email ?? 'User';

            return [
                'id' => $u->id,
                'name' => $name,
                'role_slug' => method_exists($u, 'primaryRoleName') ? $u->primaryRoleName() : ($u->role ?? ''),
            ];
        });

        return response()->json($users);
    }

    // ======================
    // Access control
    // ======================
    private function ensureCanAccess($user, int $conversationId)
    {
        // ✅ Admin الحقيقي يشوف/يدخل أي محادثة
        if ($this->isRealAdmin($user)) return true;

        // ✅ غير الأدمن لازم يكون Participant
        $exists = ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', $user->id)
            ->exists();

        abort_unless($exists, 403);
        return true;
    }

    private function ensureCanSendInConversation($user, int $conversationId): void
    {
        // admin الحقيقي مسموح
        if ($this->isRealAdmin($user)) return;

        $participantIds = ConversationParticipant::where('conversation_id', $conversationId)
            ->pluck('user_id')
            ->all();

        $others = User::whereIn('id', $participantIds)
            ->where('id', '!=', $user->id)
            ->get(['id','role','first_name','last_name','email']);

        foreach ($others as $o) {
            if (!$this->canStartChat($user, $o)) {
                abort(403, 'Not allowed to message this user');
            }
        }
    }

    // ======================
    // Role helpers
    // ======================
    private function isRealAdmin($u): bool
    {
        // Admin = admin/super admin فقط، ومش finance
        if (method_exists($u, 'isFinance') && $u->isFinance()) return false;

        // لو عندك Spatie roles
        if (method_exists($u, 'hasRole')) {
            return $u->hasRole('Admin') || $u->hasRole('Super Admin');
        }

        // fallback على دالتك الحالية
        return method_exists($u, 'isAdmin') ? (bool)$u->isAdmin() : false;
    }

    // ======================
    // Chat permission logic (YOUR RULES)
    // ======================
    private function canStartChat($a, $b): bool
    {
        if ($a->id === $b->id) return false;

        // Admin مع الجميع
        if ($this->isRealAdmin($a) || $this->isRealAdmin($b)) return true;

        // ===== Finance rules =====
        // Finance فقط مع: Admin/Teacher/Parent (ممنوع Student)
        if ($a->isFinance()) {
            return $b->isTeacher() || $b->isParentRole() || $this->isRealAdmin($b);
        }
        if ($b->isFinance()) {
            return $a->isTeacher() || $a->isParentRole() || $this->isRealAdmin($a);
        }

        // ===== Teacher rules =====
        if ($a->isTeacher()) {
            if ($b->isTeacher()) return true;
            if ($b->isStudent()) return $this->teacherTeachesStudent((int)$a->id, (int)$b->id);
            if ($b->isParentRole()) return $this->teacherCanChatParent((int)$a->id, (int)$b->id);
            return false;
        }
        if ($b->isTeacher()) {
            if ($a->isTeacher()) return true;
            if ($a->isStudent()) return $this->teacherTeachesStudent((int)$b->id, (int)$a->id);
            if ($a->isParentRole()) return $this->teacherCanChatParent((int)$b->id, (int)$a->id);
            return false;
        }

        // ===== Parent rules =====
        if ($a->isParentRole()) {
            if ($b->isTeacher()) return $this->parentCanChatTeacher((int)$a->id, (int)$b->id);
            return false;
        }
        if ($b->isParentRole()) {
            if ($a->isTeacher()) return $this->parentCanChatTeacher((int)$b->id, (int)$a->id);
            return false;
        }

        // ===== Student rules =====
        if ($a->isStudent()) {
            if ($b->isStudent()) return $this->sameClassForStudents((int)$a->id, (int)$b->id);
            return false;
        }
        if ($b->isStudent()) {
            if ($a->isStudent()) return $this->sameClassForStudents((int)$a->id, (int)$b->id);
            return false;
        }

        return false;
    }

    // ======================
    // Relationship helpers (DB-based)
    // ======================
    private function parentChildStudentIds(int $parentUserId): array
    {
        return DB::table('student_parent_infos')
            ->where('parent_user_id', $parentUserId)
            ->pluck('student_id')
            ->filter()
            ->values()
            ->all();
    }

    private function studentPlacement(int $studentId): ?object
    {
        // الأفضل promotions إذا موجودة
        if (Schema::hasTable('promotions')) {
            $row = DB::table('promotions')
                ->where('student_id', $studentId)
                ->orderByDesc('id')
                ->first(['class_id','section_id','session_id']);
            if ($row) return $row;
        }

        // fallback: آخر attendance
        $row = DB::table('attendances')
            ->where('student_id', $studentId)
            ->orderByDesc('id')
            ->first(['class_id','section_id','session_id']);

        return $row ?: null;
    }

    private function teacherAssignments(int $teacherId): array
    {
        return DB::table('assigned_teachers')
            ->where('teacher_id', $teacherId)
            ->get(['class_id','section_id','session_id'])
            ->map(function ($r) {
                return "{$r->session_id}|{$r->class_id}|{$r->section_id}";
            })
            ->unique()
            ->values()
            ->all();
    }

    private function teacherTeachesStudent(int $teacherId, int $studentId): bool
    {
        $p = $this->studentPlacement($studentId);
        if (!$p) return false;

        $key = "{$p->session_id}|{$p->class_id}|{$p->section_id}";
        return in_array($key, $this->teacherAssignments($teacherId), true);
    }

    private function studentsOfTeacher(int $teacherId): array
    {
        $keys = $this->teacherAssignments($teacherId);
        if (empty($keys)) return [];

        $pairs = collect($keys)->map(function ($k) {
            [$session_id,$class_id,$section_id] = explode('|', $k);
            return [
                'session_id' => (int)$session_id,
                'class_id'   => (int)$class_id,
                'section_id' => (int)$section_id,
            ];
        });

        // promotions لو موجودة
        if (Schema::hasTable('promotions')) {
            $q = DB::table('promotions')->select('student_id')->distinct();
            $q->where(function ($w) use ($pairs) {
                foreach ($pairs as $p) {
                    $w->orWhere(function ($x) use ($p) {
                        $x->where('session_id', $p['session_id'])
                            ->where('class_id', $p['class_id'])
                            ->where('section_id', $p['section_id']);
                    });
                }
            });
            return $q->pluck('student_id')->filter()->values()->all();
        }

        // fallback attendances
        $q = DB::table('attendances')->select('student_id')->distinct();
        $q->where(function ($w) use ($pairs) {
            foreach ($pairs as $p) {
                $w->orWhere(function ($x) use ($p) {
                    $x->where('session_id', $p['session_id'])
                        ->where('class_id', $p['class_id'])
                        ->where('section_id', $p['section_id']);
                });
            }
        });
        return $q->pluck('student_id')->filter()->values()->all();
    }

    private function teacherCanChatParent(int $teacherId, int $parentUserId): bool
    {
        $children = $this->parentChildStudentIds($parentUserId);
        if (empty($children)) return false;

        $teacherStudents = $this->studentsOfTeacher($teacherId);
        if (empty($teacherStudents)) return false;

        return count(array_intersect($children, $teacherStudents)) > 0;
    }

    private function parentCanChatTeacher(int $parentUserId, int $teacherId): bool
    {
        return $this->teacherCanChatParent($teacherId, $parentUserId);
    }

    private function sameClassForStudents(int $studentA, int $studentB): bool
    {
        $pa = $this->studentPlacement($studentA);
        $pb = $this->studentPlacement($studentB);
        if (!$pa || !$pb) return false;

        return (int)$pa->session_id === (int)$pb->session_id
            && (int)$pa->class_id   === (int)$pb->class_id
            && (int)$pa->section_id === (int)$pb->section_id;
    }

    // ======================
    // Conversation type
    // ======================
    private function inferType($a, $b): string
    {
        $ra = method_exists($a, 'primaryRoleName') ? $a->primaryRoleName() : ($a->role ?? '');
        $rb = method_exists($b, 'primaryRoleName') ? $b->primaryRoleName() : ($b->role ?? '');
        $roles = [$ra, $rb];
        sort($roles);
        $pair = implode('_', $roles);

        switch ($pair) {
            case 'parent_teacher':
                return 'parent_teacher';
            case 'student_teacher':
                return 'student_teacher';
            case 'teacher_teacher':
                return 'teacher_teacher';
            default:
                return 'general';
        }
    }
}
