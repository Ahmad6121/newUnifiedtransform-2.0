<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentAnswer;
use App\Models\AssessmentResult;
use App\Models\SchoolSession;
use Illuminate\Http\Request;

class AssessmentTakeController extends Controller
{
    private function currentSessionId(): int
    {
        if (session()->has('browse_session_id')) return (int) session('browse_session_id');
        return (int) (SchoolSession::latest()->value('id') ?? 0);
    }

    public function available()
    {
        if (auth()->user()->role !== 'student') abort(403);

        $sessionId = $this->currentSessionId();

        // الطالب لازم يكون عنده Promotion في الـ session الحالي
        $promotion = \App\Models\Promotion::where('session_id', $sessionId)
            ->where('student_id', auth()->id())
            ->first();

        if (!$promotion) {
            return view('student.assessments.available', [
                'assessments' => collect(),
                'warning' => 'No class/section assigned for current session.'
            ]);
        }

        $now = now();

        $assessments = \App\Models\Assessment::query()
            ->where('session_id', $sessionId)
            ->where('status', 'published')

            // Targeting: نفس الصف والشعبة
            ->where(function($q) use ($promotion) {
                $q->whereNull('class_id')->orWhere('class_id', $promotion->class_id);
            })
            ->where(function($q) use ($promotion) {
                $q->whereNull('section_id')->orWhere('section_id', $promotion->section_id);
            })

            // وقت الامتحان
            ->where(function($q) use ($now) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
            })
            ->where(function($q) use ($now) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
            })

            ->orderByDesc('id')
            ->paginate(15);

        return view('student.assessments.available', compact('assessments'));
    }


    public function start($assessmentId)
    {
        if (auth()->user()->role !== 'student') abort(403);

        $assessment = Assessment::findOrFail($assessmentId);

        if ($assessment->status !== 'published') {
            return back()->withErrors(['status' => 'This assessment is not published.']);
        }

        // Attempts limit
        $used = AssessmentAttempt::where('assessment_id', $assessment->id)
            ->where('student_id', auth()->id())
            ->count();

        if ($used >= (int)$assessment->attempts_allowed) {
            return redirect()->route('student.assessments.available')
                ->withErrors(['attempts' => 'No attempts left for this assessment.']);
        }

        $attempt = AssessmentAttempt::create([
            'assessment_id' => $assessment->id,
            'student_id' => auth()->id(),
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        return redirect()->route('student.assessments.take', $attempt->id);
    }

    public function take($attemptId)
    {
        if (auth()->user()->role !== 'student') abort(403);

        $attempt = AssessmentAttempt::with('assessment.questions.options')->findOrFail($attemptId);

        if ((int)$attempt->student_id !== (int)auth()->id()) abort(403);

        $assessment = $attempt->assessment;

        if ($attempt->status !== 'in_progress') {
            return redirect()->route('student.assessments.result', $attempt->id);
        }

        $questions = $assessment->questions;

        if ($assessment->is_randomized) {
            $questions = $questions->shuffle();
        }

        return view('student.assessments.take', compact('attempt','assessment','questions'));
    }

    public function submit(Request $request, $attemptId)
    {
        if (auth()->user()->role !== 'student') abort(403);

        $attempt = AssessmentAttempt::with('assessment.questions.options')->findOrFail($attemptId);
        if ((int)$attempt->student_id !== (int)auth()->id()) abort(403);

        if ($attempt->status !== 'in_progress') {
            return redirect()->route('student.assessments.result', $attempt->id);
        }

        $assessment = $attempt->assessment;

        // (اختياري) enforce duration
        if ($assessment->duration_minutes && $attempt->started_at) {
            $deadline = $attempt->started_at->copy()->addMinutes((int)$assessment->duration_minutes);
            // ما رح نرفض — بس بنسجل submit طبيعي
        }

        $autoTotal = 0.0;
        $manualTotal = 0.0;

        foreach ($assessment->questions as $q) {
            $qid = $q->id;

            // create/update answer
            $answer = AssessmentAnswer::firstOrNew([
                'attempt_id' => $attempt->id,
                'question_id' => $qid,
            ]);

            $answer->student_id = auth()->id();
            $answer->selected_option_id = null;
            $answer->answer_text = null;
            $answer->hotspot_x = null;
            $answer->hotspot_y = null;
            $answer->marks_obtained = 0;
            $answer->is_auto_graded = false;

            // grading per type
            if (in_array($q->question_type, ['mcq','true_false'])) {
                $selected = $request->input("mcq.$qid");
                $answer->selected_option_id = $selected ? (int)$selected : null;

                $correctOpt = $q->options->firstWhere('is_correct', true);
                $isCorrect = $correctOpt && $answer->selected_option_id && ((int)$correctOpt->id === (int)$answer->selected_option_id);

                $answer->marks_obtained = $isCorrect ? (float)$q->marks : 0.0;
                $answer->is_auto_graded = true;
                $autoTotal += (float)$answer->marks_obtained;
            }
            elseif ($q->question_type === 'fill_blank') {
                $txt = (string)$request->input("fill.$qid", '');
                $answer->answer_text = $txt;

                $given = mb_strtolower(trim($txt));
                $correct = mb_strtolower(trim((string)$q->correct_text));

                $isCorrect = ($correct !== '' && $given !== '' && $given === $correct);

                $answer->marks_obtained = $isCorrect ? (float)$q->marks : 0.0;
                $answer->is_auto_graded = true;
                $autoTotal += (float)$answer->marks_obtained;
            }
            elseif ($q->question_type === 'hotspot') {
                $x = $request->input("hotspot_x.$qid");
                $y = $request->input("hotspot_y.$qid");

                $answer->hotspot_x = $x !== null ? (int)$x : null;
                $answer->hotspot_y = $y !== null ? (int)$y : null;

                $isCorrect = false;
                if ($answer->hotspot_x !== null && $answer->hotspot_y !== null &&
                    $q->hotspot_x !== null && $q->hotspot_y !== null && $q->hotspot_radius !== null) {
                    $dx = ((int)$answer->hotspot_x - (int)$q->hotspot_x);
                    $dy = ((int)$answer->hotspot_y - (int)$q->hotspot_y);
                    $dist = sqrt($dx*$dx + $dy*$dy);
                    $isCorrect = ($dist <= (int)$q->hotspot_radius);
                }

                $answer->marks_obtained = $isCorrect ? (float)$q->marks : 0.0;
                $answer->is_auto_graded = true;
                $autoTotal += (float)$answer->marks_obtained;
            }
            else {
                // essay -> manual
                $txt = (string)$request->input("essay.$qid", '');
                $answer->answer_text = $txt;
                $answer->is_auto_graded = false;
                $answer->marks_obtained = 0.0;
                $manualTotal += 0.0;
            }

            $answer->save();
        }

        // save attempt summary
        $attempt->auto_marks = $autoTotal;
        $attempt->manual_marks = $manualTotal; // will be updated by teacher
        $attempt->total_marks_obtained = $autoTotal + $manualTotal;
        $attempt->submitted_at = now();
        $attempt->status = 'submitted';
        $attempt->save();

        // create/update result row
        AssessmentResult::updateOrCreate(
            ['assessment_id' => $assessment->id, 'student_id' => auth()->id()],
            [
                'marks_obtained' => $attempt->total_marks_obtained,
                'status' => 'submitted',
                'graded_by' => null
            ]
        );

        return redirect()->route('student.assessments.result', $attempt->id)
            ->with('status', 'Submitted successfully!');
    }

    public function result($attemptId)
    {
        $attempt = AssessmentAttempt::with('assessment.questions','answers.question','answers.selectedOption')->findOrFail($attemptId);

        // student only sees his
        if (auth()->user()->role === 'student' && (int)$attempt->student_id !== (int)auth()->id()) abort(403);

        $assessment = $attempt->assessment;

        // student can't see if results not published (unless admin/teacher)
        $canSee = true;
        if (auth()->user()->role === 'student') {
            $canSee = (bool)$assessment->results_published;
        }

        return view('student.assessments.result', compact('attempt','assessment','canSee'));
    }
}
