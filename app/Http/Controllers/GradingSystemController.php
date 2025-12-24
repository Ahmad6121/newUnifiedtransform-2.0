<?php

namespace App\Http\Controllers;

use App\Models\GradingSystem;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Support\ColumnHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradingSystemController extends Controller
{
    private function ensureAdmin(): void
    {
        abort_unless(Auth::user()->role === 'admin', 403);
    }

    public function create()
    {
        $this->ensureAdmin();

        $classNameCol = ColumnHelper::firstExisting('school_classes', ['name','class_name','class','title'], 'id');
        $semesterNameCol = ColumnHelper::firstExisting('semesters', ['name','semester_name','title'], 'id');

        $classes = SchoolClass::orderBy($classNameCol)->get();
        $semesters = Semester::orderBy($semesterNameCol)->get();

        return view('exams.grade.create', compact('classes','semesters','classNameCol','semesterNameCol'));
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'class_id' => 'nullable|exists:school_classes,id',
            'semester_id' => 'nullable|exists:semesters,id',
        ]);

        GradingSystem::create($data);

        return redirect()->route('exam.grade.system.index')->with('success', 'Grading system created.');
    }

    public function index()
    {
        $this->ensureAdmin();

        $classNameCol = ColumnHelper::firstExisting('school_classes', ['name','class_name','class','title'], 'id');
        $semesterNameCol = ColumnHelper::firstExisting('semesters', ['name','semester_name','title'], 'id');

        $systems = GradingSystem::with(['class','semester'])->latest()->get();

        return view('exams.grade.view', compact('systems','classNameCol','semesterNameCol'));
    }
}
