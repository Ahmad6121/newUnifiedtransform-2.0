<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentQuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssessmentQuestionController extends Controller
{
    public function index($assessmentId)
    {
        $assessment = Assessment::with('questions.options')->findOrFail($assessmentId);

        if (!in_array(auth()->user()->role, ['admin','teacher'])) abort(403);
        if (auth()->user()->role === 'teacher' && (int)$assessment->teacher_id !== (int)auth()->id()) abort(403);

        return view('assessments.questions', compact('assessment'));
    }

    public function store(Request $request, $assessmentId)
    {
        $assessment = Assessment::findOrFail($assessmentId);

        if (!in_array(auth()->user()->role, ['admin','teacher'])) abort(403);
        if (auth()->user()->role === 'teacher' && (int)$assessment->teacher_id !== (int)auth()->id()) abort(403);

        if ($assessment->mode !== 'online') {
            return back()->withErrors(['mode' => 'This assessment is not online mode.']);
        }

        $data = $request->validate([
            'question_type' => 'required|in:mcq,true_false,essay,fill_blank,hotspot',
            'question_text' => 'required|string',
            'marks' => 'required|numeric|min:0',
            'order' => 'nullable|integer|min:1',
            'image' => 'nullable|image|max:4096',

            // MCQ/TF options
            'options' => 'nullable|array',
            'options.*' => 'nullable|string',
            'correct_index' => 'nullable|integer|min:0',

            // fill blank
            'correct_text' => 'nullable|string|max:255',

            // hotspot
            'hotspot_x' => 'nullable|integer|min:0',
            'hotspot_y' => 'nullable|integer|min:0',
            'hotspot_radius' => 'nullable|integer|min:1|max:1000',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            // storage/app/public/assessments/...
            $imagePath = $request->file('image')->store('assessments', 'public');
        }

        $order = (int)($data['order'] ?? (($assessment->questions()->max('order') ?? 0) + 1));

        $q = AssessmentQuestion::create([
            'assessment_id' => $assessment->id,
            'question_type' => $data['question_type'],
            'question_text' => $data['question_text'],
            'image_path' => $imagePath,
            'marks' => (float)$data['marks'],
            'order' => $order,
            'correct_text' => $data['question_type'] === 'fill_blank' ? ($data['correct_text'] ?? null) : null,
            'hotspot_x' => $data['question_type'] === 'hotspot' ? ($data['hotspot_x'] ?? null) : null,
            'hotspot_y' => $data['question_type'] === 'hotspot' ? ($data['hotspot_y'] ?? null) : null,
            'hotspot_radius' => $data['question_type'] === 'hotspot' ? ($data['hotspot_radius'] ?? null) : null,
        ]);

        // Options for MCQ / TRUE_FALSE
        if (in_array($data['question_type'], ['mcq','true_false'])) {
            $opts = $request->input('options', []);
            $correctIndex = $request->input('correct_index', null);

            // true_false default options if user didn't provide
            if ($data['question_type'] === 'true_false' && count(array_filter($opts)) === 0) {
                $opts = ['True', 'False'];
                $correctIndex = 0;
            }

            foreach ($opts as $i => $optText) {
                $optText = trim((string)$optText);
                if ($optText === '') continue;

                AssessmentQuestionOption::create([
                    'question_id' => $q->id,
                    'option_text' => $optText,
                    'is_correct' => ($correctIndex !== null && (int)$correctIndex === (int)$i),
                ]);
            }
        }

        return redirect()->route('assessments.questions.index', $assessment->id)->with('status', 'Question added!');
    }

    public function destroy($id)
    {
        $q = AssessmentQuestion::with('assessment','options')->findOrFail($id);
        $assessment = $q->assessment;

        if (!in_array(auth()->user()->role, ['admin','teacher'])) abort(403);
        if (auth()->user()->role === 'teacher' && (int)$assessment->teacher_id !== (int)auth()->id()) abort(403);

        // delete image
        if ($q->image_path) {
            Storage::disk('public')->delete($q->image_path);
        }

        // delete options
        $q->options()->delete();
        $q->delete();

        return back()->with('status', 'Question deleted!');
    }
}
