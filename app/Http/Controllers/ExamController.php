<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Course;
use App\Models\Semester;
use App\Models\SchoolClass;
use App\Support\ColumnHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    private function ensureTeacherOrAdmin(): void
    {
        abort_unless(in_array(Auth::user()->role, ['admin','teacher']), 403);
    }

    public function index(Request $request)
    {
        $this->ensureTeacherOrAdmin();

        // ✅ Detect display columns dynamically
        $classNameCol = ColumnHelper::firstExisting('school_classes', ['name','class_name','class','title'], 'id');
        $semesterNameCol = ColumnHelper::firstExisting('semesters', ['name','semester_name','title'], 'id');

        $classes = SchoolClass::orderBy($classNameCol)->get();
        $semesters = Semester::orderBy($semesterNameCol)->get();

        $class_id = $request->get('class_id');
        $semester_id = $request->get('semester_id');

        $exams = collect();

        if ($class_id && $semester_id) {
            // ✅ ما بنعتمد على courses.class_id (لأنه ممكن مش موجود عندك)
            $exams = Exam::with('course')
                ->where('semester_id', $semester_id)
                ->whereHas('rules', function ($q) use ($class_id) {
                    $q->where('class_id', $class_id);
                })
                ->latest()
                ->get();
        }

        return view('exams.view', compact(
            'classes','semesters','exams','class_id','semester_id',
            'classNameCol','semesterNameCol'
        ));
    }

    public function create()
    {
        $this->ensureTeacherOrAdmin();

        $courseNameCol = ColumnHelper::firstExisting('courses', ['name','course_name','title'], 'id');
        $semesterNameCol = ColumnHelper::firstExisting('semesters', ['name','semester_name','title'], 'id');

        $courses = Course::orderBy($courseNameCol)->get();
        $semesters = Semester::orderBy($semesterNameCol)->get();

        return view('exams.create', compact('courses','semesters','courseNameCol','semesterNameCol'));
    }

    public function store(Request $request)
    {
        $this->ensureTeacherOrAdmin();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'semester_id' => 'nullable|exists:semesters,id',
            'starts' => 'nullable|date',
            'ends' => 'nullable|date|after_or_equal:starts',

            'is_online' => 'nullable|boolean',
            'duration_minutes' => 'nullable|integer|min:1|max:600',
            'max_attempts' => 'nullable|integer|min:1|max:20',
        ]);

        $data['is_online'] = (bool)($request->input('is_online', false));
        $data['max_attempts'] = $data['max_attempts'] ?? 1;

        Exam::create($data);

        return redirect()->route('exam.list.show')->with('success', 'Exam created.');
    }
}
