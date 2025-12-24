<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentResult;
use App\Models\Course;
use App\Models\Promotion;
use App\Models\SchoolSession;

class StudentGradesController extends Controller
{
    private function currentSessionId(): int
    {
        if (session()->has('browse_session_id')) return (int) session('browse_session_id');
        return (int) (SchoolSession::latest()->value('id') ?? 0);
    }

    public function index()
    {
        if (auth()->user()->role !== 'student') abort(403);

        $sessionId = $this->currentSessionId();

        $promotion = Promotion::where('session_id', $sessionId)
            ->where('student_id', auth()->id())
            ->first();

        if (!$promotion) {
            return view('student.grades.index', [
                'courses' => collect(),
                'rows' => [],
                'warning' => 'No class/section assigned for current session.'
            ]);
        }

        // courses حسب class_id + session_id (مثل migration عندك)
        $courses = Course::where('session_id', $sessionId)
            ->where('class_id', $promotion->class_id)
            ->orderBy('course_name')
            ->get();

        $rows = [];

        foreach ($courses as $course) {
            $assessments = Assessment::where('session_id', $sessionId)
                ->where('course_id', $course->id)
                ->where('status', 'published')
                ->where('results_published', true)
                ->get();

            $weightsSum = (float) $assessments->sum('weight_percent');
            if ($weightsSum <= 0) $weightsSum = 100.0;

            $final = 0.0;

            foreach ($assessments as $a) {
                $res = AssessmentResult::where('assessment_id', $a->id)
                    ->where('student_id', auth()->id())
                    ->first();

                $mark = (float) ($res->marks_obtained ?? 0);
                $scorePercent = ($a->total_marks > 0) ? ($mark / (float)$a->total_marks) * 100.0 : 0.0;

                $normalizedWeight = ((float)$a->weight_percent / $weightsSum) * 100.0;
                $final += ($scorePercent * $normalizedWeight) / 100.0;
            }

            $rows[] = [
                'course' => $course,
                'assessments_count' => $assessments->count(),
                'final' => round($final, 2),
            ];
        }

        return view('student.grades.index', compact('courses','rows'));
    }
}
