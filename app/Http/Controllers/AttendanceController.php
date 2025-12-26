<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Models\StudentParentInfo;
use App\Interfaces\SchoolSessionInterface;
use App\Traits\SchoolSession as SchoolSessionTrait;

class AttendanceController extends Controller
{
    use SchoolSessionTrait;

    /** @var SchoolSessionInterface */
    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository)
    {
        $this->middleware('auth');
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    public function showStudentAttendance($id)
    {
        $user = Auth::user();
        $studentId = (int) $id;

        // ✅ Student: يشوف حضوره فقط
        if ($this->isStudent($user)) {
            abort_unless($studentId === (int) $user->id, 403);
        }

        // ✅ Parent: يشوف حضور أولاده فقط
        if ($this->isParent($user)) {
            $isMyChild = StudentParentInfo::where('parent_user_id', (int) $user->id)
                ->where('student_id', $studentId)
                ->exists();

            abort_unless($isMyChild, 403);
        }

        // ✅ Admin/Teacher: مسموح (لا تعمل شي)

        return $this->renderStudentAttendanceView($studentId);
    }

    private function renderStudentAttendanceView(int $studentId)
    {
        $sessionId = $this->getSchoolCurrentSession();

        // ✅ الطالب كـ User object عشان الفيو ما يضرب
        $student = \App\Models\User::findOrFail($studentId);

        $rows = collect();

        if (\Illuminate\Support\Facades\Schema::hasTable('attendances')) {
            $q = \Illuminate\Support\Facades\DB::table('attendances');

            // أعمدة الطالب (حسب الموجود)
            if (\Illuminate\Support\Facades\Schema::hasColumn('attendances', 'student_id')) {
                $q->where('student_id', $studentId);
            } elseif (\Illuminate\Support\Facades\Schema::hasColumn('attendances', 'user_id')) {
                $q->where('user_id', $studentId);
            } elseif (\Illuminate\Support\Facades\Schema::hasColumn('attendances', 'student_user_id')) {
                $q->where('student_user_id', $studentId);
            }

            // فلترة السيشن إذا موجودة
            if ($sessionId && \Illuminate\Support\Facades\Schema::hasColumn('attendances', 'session_id')) {
                $q->where('session_id', $sessionId);
            }

            // ترتيب
            if (\Illuminate\Support\Facades\Schema::hasColumn('attendances', 'date')) {
                $q->orderBy('date', 'desc');
            } elseif (\Illuminate\Support\Facades\Schema::hasColumn('attendances', 'attendance_date')) {
                $q->orderBy('attendance_date', 'desc');
            } else {
                $q->orderBy('id', 'desc');
            }

            $rows = $q->get();
        }

        return view('attendances.attendance', [
            // ✅ أهم سطرين للفيو
            'student'   => $student,
            'studentId' => $studentId,

            'current_school_session_id' => $sessionId,

            // عشان أي أسماء قديمة داخل الفيو
            'attendance'  => $rows,
            'attendances' => $rows,
            'records'     => $rows,
        ]);
    }


    // ===== Helpers =====
    private function isStudent($user): bool
    {
        return (($user->role ?? null) === 'student')
            || (method_exists($user, 'hasRole') && $user->hasRole('student'));
    }

    private function isParent($user): bool
    {
        return (($user->role ?? null) === 'parent')
            || (method_exists($user, 'hasRole') && $user->hasRole('parent'));
    }
}
