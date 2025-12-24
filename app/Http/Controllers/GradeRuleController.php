<?php

namespace App\Http\Controllers;

use App\Models\GradeRule;
use App\Models\GradingSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeRuleController extends Controller
{
    private function ensureAdmin(): void
    {
        abort_unless(Auth::user()->role === 'admin', 403);
    }

    public function create()
    {
        $this->ensureAdmin();
        $systems = GradingSystem::latest()->get();
        return view('exams.grade.add-rule', compact('systems'));
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'grading_system_id' => 'required|exists:grading_systems,id',
            'min_percent' => 'required|numeric|min:0|max:100',
            'max_percent' => 'required|numeric|min:0|max:100|gte:min_percent',
            'grade' => 'required|string|max:20',
            'remark' => 'nullable|string|max:255',
        ]);

        GradeRule::create($data);

        return back()->with('success', 'Grade rule added.');
    }

    public function index()
    {
        $this->ensureAdmin();
        $rules = GradeRule::with('system')->latest()->get();
        return view('exams.grade.view-rules', compact('rules'));
    }

    public function destroy(Request $request)
    {
        $this->ensureAdmin();

        $request->validate(['id' => 'required|exists:grade_rules,id']);
        GradeRule::where('id', $request->id)->delete();

        return back()->with('success', 'Rule deleted.');
    }
}
