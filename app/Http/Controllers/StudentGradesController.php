<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentResult;
use App\Models\Course;
use App\Models\Promotion;
use App\Models\SchoolSession;
use Illuminate\Support\Facades\Auth;

class StudentGradesController extends Controller
{
    private function currentSessionId(): int
    {
        if (session()->has('browse_session_id')) return (int) session('browse_session_id');
        return (int) (SchoolSession::latest()->value('id') ?? 0);
    }

    public function index()
    {
        $student = Auth::user();
        if (!$student || (($student->role ?? '') !== 'student')) abort(403);

        $sessionId = $this->currentSessionId();

        // الطالب لازم يكون عنده Promotion بنفس السيشن
        $promotion = Promotion::where('session_id', $sessionId)
            ->where('student_id', $student->id)
            ->first();

        if (!$promotion) {
            return view('student.grades.index', [
                'student' => $student,
                'rows' => [],
                'note' => 'No class/promotion found for current session.'
            ]);
        }

        // كورسات الصف
        $courses = Course::where('session_id', $sessionId)
            ->where('class_id', $promotion->class_id)
            ->orderBy('course_name')
            ->get();

        $rows = [];

        foreach ($courses as $course) {
            // نفس شروط Report Card (published + results_published)
            $assessments = Assessment::where('session_id', $sessionId)
                ->where('course_id', $course->id)
                ->where('status', 'published')
                ->where('results_published', true)
                ->orderBy('id')
                ->get();

            if ($assessments->isEmpty()) continue;

            foreach ($assessments as $a) {
                $res = AssessmentResult::where('assessment_id', $a->id)
                    ->where('student_id', $student->id)
                    ->first();

                // إذا ما في نتيجة لهالـ assessment نتجاهله
                if (!$res) continue;

                $mark = (float)($res->marks_obtained ?? 0);
                $total = (float)($a->total_marks ?? 0);
                $percent = $total > 0 ? round(($mark / $total) * 100, 2) : 0;

                $rows[] = [
                    'course' => $course->course_name,
                    'assessment' => $a->title,
                    'type' => $a->kind,
                    'mark' => $mark,
                    'total' => $total,
                    'weight' => (float)($a->weight_percent ?? 0),
                    'percent' => $percent,
                ];
            }
        }

        return view('student.grades.index', [
            'student' => $student,
            'rows' => $rows,
            'note' => null
        ]);
    }
}
