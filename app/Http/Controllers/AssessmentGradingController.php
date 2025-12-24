<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentAnswer;
use App\Models\AssessmentResult;
use Illuminate\Http\Request;

class AssessmentGradingController extends Controller
{
    public function attempts($assessmentId)
    {
        if (!in_array(auth()->user()->role, ['admin','teacher'])) abort(403);

        $assessment = Assessment::findOrFail($assessmentId);

        if (auth()->user()->role === 'teacher' && (int)$assessment->teacher_id !== (int)auth()->id()) abort(403);

        $attempts = AssessmentAttempt::with('student')
            ->where('assessment_id', $assessment->id)
            ->orderByDesc('id')
            ->paginate(20);

        return view('assessments.attempts', compact('assessment','attempts'));
    }

    public function review($attemptId)
    {
        if (!in_array(auth()->user()->role, ['admin','teacher'])) abort(403);

        $attempt = AssessmentAttempt::with('assessment.questions.options','answers.question','student')->findOrFail($attemptId);
        $assessment = $attempt->assessment;

        if (auth()->user()->role === 'teacher' && (int)$assessment->teacher_id !== (int)auth()->id()) abort(403);

        return view('assessments.review', compact('attempt','assessment'));
    }

    public function gradeAnswer(Request $request, $answerId)
    {
        if (!in_array(auth()->user()->role, ['admin','teacher'])) abort(403);

        $answer = AssessmentAnswer::with('attempt.assessment','question')->findOrFail($answerId);
        $assessment = $answer->attempt->assessment;

        if (auth()->user()->role === 'teacher' && (int)$assessment->teacher_id !== (int)auth()->id()) abort(403);

        // only manual questions (essay) should be graded here
        $max = (float)$answer->question->marks;

        $data = $request->validate([
            'marks_obtained' => 'required|numeric|min:0',
        ]);

        $marks = (float)$data['marks_obtained'];
        if ($marks > $max) $marks = $max;

        $answer->marks_obtained = $marks;
        $answer->is_auto_graded = false;
        $answer->save();

        return back()->with('status', 'Answer graded!');
    }

    public function finalize(Request $request, $attemptId)
    {
        if (!in_array(auth()->user()->role, ['admin','teacher'])) abort(403);

        $attempt = AssessmentAttempt::with('assessment.questions','answers.question')->findOrFail($attemptId);
        $assessment = $attempt->assessment;

        if (auth()->user()->role === 'teacher' && (int)$assessment->teacher_id !== (int)auth()->id()) abort(403);

        // sum
        $auto = 0.0;
        $manual = 0.0;

        foreach ($attempt->answers as $ans) {
            if ($ans->is_auto_graded) $auto += (float)$ans->marks_obtained;
            else $manual += (float)$ans->marks_obtained;
        }

        $attempt->auto_marks = $auto;
        $attempt->manual_marks = $manual;
        $attempt->total_marks_obtained = $auto + $manual;
        $attempt->status = 'graded';
        $attempt->save();

        AssessmentResult::updateOrCreate(
            ['assessment_id' => $assessment->id, 'student_id' => $attempt->student_id],
            [
                'marks_obtained' => $attempt->total_marks_obtained,
                'status' => 'graded',
                'graded_by' => auth()->id()
            ]
        );

        return back()->with('status', 'Attempt finalized!');
    }
}
