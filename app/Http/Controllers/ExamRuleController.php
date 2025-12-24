<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamRule;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Support\ColumnHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamRuleController extends Controller
{
    private function ensureTeacherOrAdmin(): void
    {
        abort_unless(in_array(Auth::user()->role, ['admin','teacher']), 403);
    }

    public function create()
    {
        $this->ensureTeacherOrAdmin();

        $classNameCol = ColumnHelper::firstExisting('school_classes', ['name','class_name','class','title'], 'id');
        $sectionNameCol = ColumnHelper::firstExisting('sections', ['name','section_name','title'], 'id');

        $exams = Exam::latest()->get();
        $classes = SchoolClass::orderBy($classNameCol)->get();
        $sections = Section::orderBy($sectionNameCol)->get();

        return view('exams.add-rule', compact('exams','classes','sections','classNameCol','sectionNameCol'));
    }

    public function store(Request $request)
    {
        $this->ensureTeacherOrAdmin();

        $data = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|exists:school_classes,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        ExamRule::updateOrCreate($data, $data);

        return back()->with('success', 'Rule added.');
    }

    public function index(Request $request)
    {
        $this->ensureTeacherOrAdmin();

        $classNameCol = ColumnHelper::firstExisting('school_classes', ['name','class_name','class','title'], 'id');
        $sectionNameCol = ColumnHelper::firstExisting('sections', ['name','section_name','title'], 'id');

        $exam_id = $request->get('exam_id');
        $exams = Exam::latest()->get();

        $rules = collect();
        if ($exam_id) {
            $rules = ExamRule::with(['class','section','exam'])
                ->where('exam_id', $exam_id)
                ->latest()
                ->get();
        }

        return view('exams.view-rule', compact('exams','rules','exam_id','classNameCol','sectionNameCol'));
    }
}
