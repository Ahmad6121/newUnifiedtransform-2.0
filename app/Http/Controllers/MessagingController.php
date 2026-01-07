<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessagingController extends Controller
{
    public function index()
    {
        return view('messages.index');
    }

    // قائمة المحادثات
    public function conversations(Request $r)
    {
        $user = $r->user();

        $convs = Conversation::query()
            // ✅ فقط الأدمن يشوف كل المحادثات
            // ✅ المحاسب (finance) + باقي الأدوار يشوفوا فقط اللي هم مشاركين فيها
            ->when(!$user->isAdmin(), function ($q) use ($user) {
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

                // ✅ لا نحسب unread إلا لو المستخدم مشارك بالمحادثة (أو الأدمن مشارك)
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

    // رسائل محادثة
    public function messages(Request $r, $conversationId)
    {
        $user = $r->user();
        $this->ensureCanAccess($user, $conversationId);

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

    // بدء محادثة 1-1
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

    // إرسال رسالة
    public function send(Request $r, $conversationId)
    {
        $user = $r->user();
        $this->ensureCanAccess($user, $conversationId);

        $r->validate([
            'body' => 'required|string|max:4000',
        ]);

        // ✅ لو الأدمن يبعت على محادثة وهو مش Participant (مراقبة)، نخليه ينضم تلقائي
        if ($user->isAdmin()) {
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

    // Admin مراقبة/دخول (اختياري)
    public function adminJoin(Request $r, $conversationId)
    {
        $user = $r->user();
        if (!$user->isAdmin()) return response()->json(['message' => 'Forbidden'], 403);

        ConversationParticipant::firstOrCreate(
            ['conversation_id' => $conversationId, 'user_id' => $user->id],
            ['is_admin_observer' => true, 'last_read_at' => now()]
        );

        return response()->json(['ok' => true]);
    }

    // بحث مستخدمين لبدء محادثة
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
            ->limit(30)
            ->get(['id','first_name','last_name','email','role']);

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
                'role_slug' => $u->primaryRoleName(),
            ];
        });

        return response()->json($users);
    }

    // ======================
    // Helpers
    // ======================

    private function ensureCanAccess($user, $conversationId)
    {
        // ✅ فقط الأدمن يشوف/يدخل أي محادثة
        if ($user->isAdmin()) return true;

        // ✅ المحاسب (finance) لازم يكون participant عشان يدخل
        $exists = ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', $user->id)
            ->exists();

        abort_unless($exists, 403);
        return true;
    }

    private function canStartChat($a, $b): bool
    {
        // finance مع الجميع (مسموح يبدأ محادثة، لكن لن يرى إلا محادثاته لأنه participant فيها)
        if ($a->isFinance() || $b->isFinance()) return true;

        // admin مع الجميع
        if ($a->isAdmin() || $b->isAdmin()) return true;

        // teacher مع: student/parent/teacher
        if ($a->isTeacher() && ($b->isStudent() || $b->isParentRole() || $b->isTeacher())) return true;
        if ($b->isTeacher() && ($a->isStudent() || $a->isParentRole() || $a->isTeacher())) return true;

        // parent مع teacher
        if ($a->isParentRole() && $b->isTeacher()) return true;
        if ($b->isParentRole() && $a->isTeacher()) return true;

        // student مع teacher
        if ($a->isStudent() && $b->isTeacher()) return true;
        if ($b->isStudent() && $a->isTeacher()) return true;

        return false;
    }

    private function inferType($a, $b): string
    {
        $ra = $a->primaryRoleName();
        $rb = $b->primaryRoleName();
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
