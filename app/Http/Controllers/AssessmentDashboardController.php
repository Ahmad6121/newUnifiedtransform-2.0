<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentResult;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use Illuminate\Support\Carbon;

class AssessmentDashboardController extends Controller
{
    private function currentSessionId(): int
    {
        if (session()->has('browse_session_id')) return (int) session('browse_session_id');
        return (int) (SchoolSession::latest()->value('id') ?? 0);
    }

    public function index()
    {
        if (!in_array(auth()->user()->role, ['admin', 'teacher'])) abort(403);

        $sessionId = $this->currentSessionId();
        $now = Carbon::now();

        $q = Assessment::where('session_id', $sessionId);

        if (auth()->user()->role === 'teacher') {
            $q->where('teacher_id', auth()->id());
        }

        $assessments = $q->orderByDesc('id')->take(50)->get();

        // إجماليات
        $total = $assessments->count();
        $published = $assessments->where('status', 'published')->count();
        $resultsPublished = $assessments->where('results_published', true)->count();

        $upcoming = $assessments->filter(function ($a) use ($now) {
            return $a->status === 'published'
                && $a->start_date
                && Carbon::parse($a->start_date)->gt($now);
        })->count();

        // جلب أسماء الكلاسات والكورسات بدون N+1
        $courseIds = $assessments->pluck('course_id')->filter()->unique()->values();
        $classIds  = $assessments->pluck('class_id')->filter()->unique()->values();

        $coursesMap = Course::whereIn('id', $courseIds)->pluck('course_name', 'id');
        $classesMap = SchoolClass::whereIn('id', $classIds)->pluck('class_name', 'id');

        // نتائج كل assessments مرة وحدة
        $resultsByAssessment = AssessmentResult::whereIn('assessment_id', $assessments->pluck('id'))
            ->get()
            ->groupBy('assessment_id');

        $rows = [];
        $chartLabels = [];
        $chartAverages = [];

        foreach ($assessments as $a) {
            $results = $resultsByAssessment->get($a->id, collect());

            $gradedCount = $results->count();
            $avgPercent = 0;
            $passRate = 0;

            $totalMarks = (float) ($a->total_marks ?? 0);

            if ($gradedCount > 0 && $totalMarks > 0) {
                $avgPercent = round((($results->avg('marks_obtained') ?? 0) / $totalMarks) * 100, 2);

                $passCount = $results->filter(function ($r) use ($totalMarks) {
                    $p = $totalMarks > 0 ? (((float) $r->marks_obtained / $totalMarks) * 100) : 0;
                    return $p >= 50; // أو استخدم passing_marks لو بدك
                })->count();

                $passRate = round(($passCount / $gradedCount) * 100, 2);
            }

            $courseName = $a->course_id ? ($coursesMap[$a->course_id] ?? '-') : '-';
            $className  = $a->class_id ? ($classesMap[$a->class_id] ?? '-') : '-';

            $rows[] = [
                'a' => $a,
                'course_name' => $courseName,
                'class_name' => $className,
                'graded' => $gradedCount,
                'avg_percent' => $avgPercent,
                'pass_rate' => $passRate,
            ];

            if (count($chartLabels) < 10) {
                $chartLabels[] = $a->title;
                $chartAverages[] = $avgPercent;
            }
        }

        $chartLabels = array_reverse($chartLabels);
        $chartAverages = array_reverse($chartAverages);

        return view('assessments.dashboard', compact(
            'assessments',
            'total', 'published', 'resultsPublished', 'upcoming',
            'rows', 'chartLabels', 'chartAverages'
        ));
    }
}
