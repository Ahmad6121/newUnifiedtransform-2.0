<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AssessmentMarksController extends Controller
{
    private function authorizeMarks(Assessment $assessment): void
    {
        if (!in_array(auth()->user()->role, ['admin', 'teacher'])) abort(403);

        if (auth()->user()->role === 'teacher' && (int)$assessment->teacher_id !== (int)auth()->id()) {
            abort(403);
        }
    }

    private function orderUsersQuery($query)
    {
        if (Schema::hasColumn('users', 'name')) return $query->orderBy('name');

        if (Schema::hasColumn('users', 'first_name')) {
            $query->orderBy('first_name');
            if (Schema::hasColumn('users', 'last_name')) $query->orderBy('last_name');
            return $query;
        }

        if (Schema::hasColumn('users', 'full_name')) return $query->orderBy('full_name');

        return $query->orderBy('id');
    }

    private function displayUserName($u): string
    {
        if (!$u) return '-';
        if (isset($u->name) && $u->name) return $u->name;

        $first = $u->first_name ?? '';
        $last  = $u->last_name ?? '';
        $full = trim($first . ' ' . $last);
        if ($full !== '') return $full;

        return $u->email ?? ('#' . ($u->id ?? ''));
    }

    // ✅ students from promotions (best for your DB)
    private function getTargetStudentIds(Assessment $assessment): array
    {
        $sessionId = (int)($assessment->session_id ?? 0);
        $classId   = $assessment->class_id ? (int)$assessment->class_id : null;
        $sectionId = $assessment->section_id ? (int)$assessment->section_id : null;

        if (!Schema::hasTable('promotions')) return [];

        $q = DB::table('promotions');

        if ($sessionId && Schema::hasColumn('promotions', 'session_id')) $q->where('session_id', $sessionId);
        if ($classId && Schema::hasColumn('promotions', 'class_id')) $q->where('class_id', $classId);
        if ($sectionId && Schema::hasColumn('promotions', 'section_id')) $q->where('section_id', $sectionId);

        if (!Schema::hasColumn('promotions', 'student_id')) return [];

        return $q->pluck('student_id')
            ->map(function ($x) { return (int)$x; })
            ->unique()
            ->values()
            ->toArray();
    }

    private function resultsMap(int $assessmentId): array
    {
        if (!Schema::hasTable('assessment_results')) return [];

        $rows = DB::table('assessment_results')->where('assessment_id', $assessmentId)->get();

        $map = [];
        foreach ($rows as $r) $map[(int)$r->student_id] = $r;
        return $map;
    }

    // GET /assessments/{assessment}/marks
    public function edit(Assessment $assessment)
    {
        $this->authorizeMarks($assessment);

        $studentIds = $this->getTargetStudentIds($assessment);

        if (empty($studentIds)) {
            $students = collect();
            $resultsMap = [];
            $displayUserName = function ($u) { return $this->displayUserName($u); };

            return view('assessments.marks', compact('assessment', 'students', 'resultsMap', 'displayUserName'))
                ->with('error', 'No students found for this assessment target. Check promotions mapping (session/class/section).');
        }

        $studentsQ = User::query()->whereIn('id', $studentIds)->where('role', 'student');
        $students  = $this->orderUsersQuery($studentsQ)->get();

        $resultsMap = $this->resultsMap((int)$assessment->id);
        $displayUserName = function ($u) { return $this->displayUserName($u); };

        return view('assessments.marks', compact('assessment', 'students', 'resultsMap', 'displayUserName'));
    }

    // POST /assessments/{assessment}/marks
    public function update(Request $request, Assessment $assessment)
    {
        $this->authorizeMarks($assessment);

        if (!Schema::hasTable('assessment_results')) abort(500, 'assessment_results table not found.');

        $total = (float)($assessment->total_marks ?? 100);

        // ✅ منع أقل من 0 وأعلى من total
        $data = $request->validate([
            'marks' => 'required|array',
            'marks.*' => 'nullable|numeric|min:0|max:' . $total,
        ]);

        foreach ($data['marks'] as $studentId => $mark) {
            $studentId = (int)$studentId;

            if ($mark === null || $mark === '') continue;

            $mark = (float)$mark;

            // extra safety
            if ($mark < 0) $mark = 0;
            if ($mark > $total) $mark = $total;

            $exists = DB::table('assessment_results')
                ->where('assessment_id', $assessment->id)
                ->where('student_id', $studentId)
                ->first();

            if ($exists) {
                DB::table('assessment_results')->where('id', $exists->id)->update([
                    'marks_obtained' => $mark,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('assessment_results')->insert([
                    'assessment_id' => $assessment->id,
                    'student_id' => $studentId,
                    'marks_obtained' => $mark,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('assessments.marks.edit', $assessment->id)
            ->with('success', 'Marks saved successfully.');
    }
}
