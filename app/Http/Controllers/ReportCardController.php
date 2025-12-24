<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentResult;
use App\Models\Course;
use App\Models\Promotion;
use App\Models\SchoolSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportCardController extends Controller
{
    private function currentSessionId(): int
    {
        if (session()->has('browse_session_id')) return (int) session('browse_session_id');
        return (int) (SchoolSession::latest()->value('id') ?? 0);
    }

    // Student نفسه
    public function my()
    {
        if (auth()->user()->role !== 'student') abort(403);
        return $this->buildForStudent((int)auth()->id());
    }

    // Parent/Admin يشوف تقرير طالب محدد
    public function child(User $student)
    {
        $user = auth()->user();

        // (اختياري) تأكد أنه طالب فعلاً
        if (isset($student->role) && $student->role !== 'student') {
            abort(404);
        }

        // Admin يسمح له يشوف أي طالب
        if ($user->role === 'admin') {
            return $this->buildForStudent((int)$student->id);
        }

        // Parent: لازم الطالب يكون ابنه
        if ($user->role === 'parent') {

            // ✅ الأفضل: student_parent_infos (لأن مشروعك غالبًا بستخدمه)
            if (Schema::hasTable('student_parent_infos') && Schema::hasColumn('student_parent_infos', 'parent_user_id')) {
                $studentKey = Schema::hasColumn('student_parent_infos', 'student_id') ? 'student_id' : 'user_id';

                $ok = DB::table('student_parent_infos')
                    ->where($studentKey, (int)$student->id)
                    ->where('parent_user_id', (int)$user->id)
                    ->exists();

                if (!$ok) abort(403);

                return $this->buildForStudent((int)$student->id);
            }

            // ✅ fallback: إذا عندك parent_user_id داخل users
            if (Schema::hasColumn('users', 'parent_user_id')) {
                if ((int)($student->parent_user_id ?? 0) !== (int)$user->id) {
                    abort(403);
                }
                return $this->buildForStudent((int)$student->id);
            }

            // إذا ما في أي شيء يثبت العلاقة
            abort(403);
        }

        // باقي الأدوار غير مسموح
        abort(403);
    }

    private function buildForStudent(int $studentId)
    {
        $sessionId = $this->currentSessionId();

        $promotion = Promotion::where('session_id', $sessionId)
            ->where('student_id', $studentId)
            ->first();

        if (!$promotion) {
            return view('report_card.show', [
                'student' => User::findOrFail($studentId),
                'rows' => [],
                'overall' => 0,
                'note' => 'No promotion/class found for this session.'
            ]);
        }

        $courses = Course::where('session_id', $sessionId)
            ->where('class_id', $promotion->class_id)
            ->orderBy('course_name')
            ->get();

        $rows = [];
        $sum = 0; $count = 0;

        foreach ($courses as $course) {
            $assessments = Assessment::where('session_id', $sessionId)
                ->where('course_id', $course->id)
                ->where('status', 'published')
                ->where('results_published', true)
                ->orderBy('id')
                ->get();

            if ($assessments->count() === 0) continue;

            $weightsSum = (float)$assessments->sum('weight_percent');
            if ($weightsSum <= 0) $weightsSum = 100.0;

            $items = [];
            $finalCourse = 0.0;

            foreach ($assessments as $a) {
                $res = AssessmentResult::where('assessment_id', $a->id)
                    ->where('student_id', $studentId)
                    ->first();

                $mark = (float)($res->marks_obtained ?? 0);
                $percent = ((float)$a->total_marks > 0) ? ($mark / (float)$a->total_marks) * 100.0 : 0.0;

                $normW = ((float)$a->weight_percent / $weightsSum) * 100.0;
                $finalCourse += ($percent * $normW) / 100.0;

                $items[] = [
                    'title'   => $a->title,
                    'kind'    => $a->kind,
                    'mark'    => $mark,
                    'total'   => (float)$a->total_marks,
                    'weight'  => (float)$a->weight_percent,
                    'percent' => round($percent, 2),
                ];
            }

            $finalCourse = round($finalCourse, 2);

            $rows[] = [
                'course' => $course->course_name,
                'items'  => $items,
                'final'  => $finalCourse
            ];

            $sum += $finalCourse;
            $count++;
        }

        $overall = $count ? round($sum / $count, 2) : 0;

        return view('report_card.show', [
            'student' => User::findOrFail($studentId),
            'rows'    => $rows,
            'overall' => $overall,
            'note'    => null
        ]);
    }
}
