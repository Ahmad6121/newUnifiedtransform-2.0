<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportsExportController extends Controller
{
    private function authorizeReports(): void
    {
        if (!in_array(auth()->user()->role, ['admin', 'teacher'])) {
            abort(403);
        }
    }

    private function orderUsersQuery($query)
    {
        if (Schema::hasColumn('users', 'name')) {
            return $query->orderBy('name');
        }

        if (Schema::hasColumn('users', 'first_name')) {
            $query->orderBy('first_name');
            if (Schema::hasColumn('users', 'last_name')) $query->orderBy('last_name');
            return $query;
        }

        if (Schema::hasColumn('users', 'full_name')) {
            return $query->orderBy('full_name');
        }

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

    /**
     * ✅ مصدر الطلاب الصحيح في مشروعك: promotions
     * لأن student_academic_infos عندك ما فيه class/section/session
     */
    private function getStudentIdsFromPromotions(?int $sessionId, ?int $classId, ?int $sectionId): array
    {
        if (!Schema::hasTable('promotions')) return [];

        $q = DB::table('promotions');

        // session
        if ($sessionId && Schema::hasColumn('promotions', 'session_id')) {
            $q->where('session_id', $sessionId);
        }

        // class (قد يكون class_id أو promoted_to_class)
        if ($classId) {
            if (Schema::hasColumn('promotions', 'class_id')) {
                $q->where('class_id', $classId);
            } elseif (Schema::hasColumn('promotions', 'promoted_to_class')) {
                $q->where('promoted_to_class', $classId);
            }
        }

        // section (قد يكون section_id أو promoted_to_section)
        if ($sectionId) {
            if (Schema::hasColumn('promotions', 'section_id')) {
                $q->where('section_id', $sectionId);
            } elseif (Schema::hasColumn('promotions', 'promoted_to_section')) {
                $q->where('promoted_to_section', $sectionId);
            }
        }

        // الطالب (قد يكون student_id أو student_user_id أو user_id)
        $studentCol =
            Schema::hasColumn('promotions', 'student_id') ? 'student_id' :
                (Schema::hasColumn('promotions', 'student_user_id') ? 'student_user_id' :
                    (Schema::hasColumn('promotions', 'user_id') ? 'user_id' : null));

        if (!$studentCol) return [];

        return $q->pluck($studentCol)
            ->map(fn ($x) => (int)$x)
            ->unique()
            ->values()
            ->toArray();
    }

    private function getAssessmentResultsMap(int $assessmentId): array
    {
        if (!Schema::hasTable('assessment_results')) return [];

        $rows = DB::table('assessment_results')
            ->where('assessment_id', $assessmentId)
            ->get();

        $map = [];
        foreach ($rows as $r) {
            $map[(int)$r->student_id] = $r;
        }
        return $map;
    }

    // =========================
    // 1) Assessment CSV
    // =========================
    public function assessmentCsv(Assessment $assessment)
    {
        $this->authorizeReports();

        if (auth()->user()->role === 'teacher' && (int)$assessment->teacher_id !== (int)auth()->id()) {
            abort(403);
        }

        $studentIds = $this->getStudentIdsFromPromotions(
            (int)($assessment->session_id ?? 0),
            $assessment->class_id ? (int)$assessment->class_id : null,
            $assessment->section_id ? (int)$assessment->section_id : null
        );

        $students = collect();
        if (!empty($studentIds)) {
            $studentsQ = User::query()
                ->whereIn('id', $studentIds)
                ->where('role', 'student');

            $students = $this->orderUsersQuery($studentsQ)->get();
        }

        $resultsMap = $this->getAssessmentResultsMap((int)$assessment->id);

        $filename = 'assessment_' . $assessment->id . '_results.csv';

        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($students, $assessment, $resultsMap) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['Student ID', 'Student Name', 'Marks Obtained', 'Total Marks', 'Percent', 'Pass']);

            $total = (float)($assessment->total_marks ?? 100);

            if ($students->count() === 0) {
                fputcsv($out, ['', 'No students found for this assessment target (check promotions).', '', '', '', '']);
                fclose($out);
                return;
            }

            foreach ($students as $s) {
                $res = $resultsMap[(int)$s->id] ?? null;
                $marks = $res->marks_obtained ?? null;

                $percent = ($marks !== null && $total > 0) ? round(((float)$marks / $total) * 100, 2) : '';
                $pass = ($percent !== '' && $percent >= 50) ? 'PASS' : (($percent === '') ? '' : 'FAIL');

                fputcsv($out, [
                    $s->id,
                    $this->displayUserName($s),
                    $marks,
                    $total,
                    $percent,
                    $pass,
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    // =========================
    // 2) Assessment PDF
    // =========================
    public function assessmentPdf(Assessment $assessment)
    {
        $this->authorizeReports();

        if (auth()->user()->role === 'teacher' && (int)$assessment->teacher_id !== (int)auth()->id()) {
            abort(403);
        }

        $studentIds = $this->getStudentIdsFromPromotions(
            (int)($assessment->session_id ?? 0),
            $assessment->class_id ? (int)$assessment->class_id : null,
            $assessment->section_id ? (int)$assessment->section_id : null
        );

        $students = collect();
        if (!empty($studentIds)) {
            $studentsQ = User::query()
                ->whereIn('id', $studentIds)
                ->where('role', 'student');

            $students = $this->orderUsersQuery($studentsQ)->get();
        }

        $resultsMap = $this->getAssessmentResultsMap((int)$assessment->id);

        $courseName = $assessment->course_id
            ? (Course::where('id', $assessment->course_id)->value('course_name') ?? '-')
            : '-';

        $className = $assessment->class_id
            ? (SchoolClass::where('id', $assessment->class_id)->value('class_name') ?? '-')
            : '-';

        $pdf = Pdf::loadView('reports.assessment_pdf', [
            'assessment' => $assessment,
            'students' => $students,
            'resultsMap' => $resultsMap,
            'courseName' => $courseName,
            'className' => $className,
            'displayUserName' => fn ($u) => $this->displayUserName($u),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('assessment_' . $assessment->id . '_results.pdf');
    }

    // =========================
    // 3) Class Gradebook CSV
    // =========================
    public function classGradebookCsv(Request $request)
    {
        $this->authorizeReports();

        $classId   = (int)$request->query('class_id');
        $sectionId = $request->query('section_id') ? (int)$request->query('section_id') : null;
        $courseId  = (int)$request->query('course_id');
        $sessionId = (int)$request->query('session_id', 0);

        if (!$classId || !$courseId || !$sessionId) {
            abort(422, 'Missing required params: session_id, class_id, course_id');
        }

        $studentIds = $this->getStudentIdsFromPromotions($sessionId, $classId, $sectionId);

        $students = collect();
        if (!empty($studentIds)) {
            $studentsQ = User::query()
                ->whereIn('id', $studentIds)
                ->where('role', 'student');

            $students = $this->orderUsersQuery($studentsQ)->get();
        }

        $assessments = Assessment::where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
            ->where('course_id', $courseId)
            ->orderBy('id')
            ->get();

        $filename = "gradebook_class{$classId}_course{$courseId}.csv";

        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($students, $assessments) {
            $out = fopen('php://output', 'w');

            $header = ['Student ID', 'Student Name'];
            foreach ($assessments as $a) $header[] = $a->title . " ({$a->weight_percent}%)";
            $header[] = 'Final (weighted %)';
            fputcsv($out, $header);

            if ($students->count() === 0) {
                fputcsv($out, ['', 'No students found (check promotions).']);
                fclose($out);
                return;
            }

            foreach ($students as $s) {
                $row = [$s->id, $this->displayUserName($s)];
                $final = 0;

                foreach ($assessments as $a) {
                    $res = Schema::hasTable('assessment_results')
                        ? DB::table('assessment_results')
                            ->where('assessment_id', $a->id)
                            ->where('student_id', $s->id)
                            ->first()
                        : null;

                    $marks = $res->marks_obtained ?? null;
                    $row[] = $marks;

                    $total  = (float)($a->total_marks ?? 100);
                    $weight = (float)($a->weight_percent ?? 0);

                    if ($marks !== null && $total > 0 && $weight > 0) {
                        $final += (((float)$marks / $total) * 100) * ($weight / 100);
                    }
                }

                $row[] = round($final, 2);
                fputcsv($out, $row);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    // =========================
    // 4) Class Gradebook PDF
    // =========================
    public function classGradebookPdf(Request $request)
    {
        $this->authorizeReports();

        $classId   = (int)$request->query('class_id');
        $sectionId = $request->query('section_id') ? (int)$request->query('section_id') : null;
        $courseId  = (int)$request->query('course_id');
        $sessionId = (int)$request->query('session_id', 0);

        if (!$classId || !$courseId || !$sessionId) {
            abort(422, 'Missing required params: session_id, class_id, course_id');
        }

        $studentIds = $this->getStudentIdsFromPromotions($sessionId, $classId, $sectionId);

        $students = collect();
        if (!empty($studentIds)) {
            $studentsQ = User::query()
                ->whereIn('id', $studentIds)
                ->where('role', 'student');

            $students = $this->orderUsersQuery($studentsQ)->get();
        }

        $assessments = Assessment::where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
            ->where('course_id', $courseId)
            ->orderBy('id')
            ->get();

        $courseName = Course::where('id', $courseId)->value('course_name') ?? '-';
        $className  = SchoolClass::where('id', $classId)->value('class_name') ?? '-';

        $pdf = Pdf::loadView('reports.class_gradebook_pdf', [
            'students' => $students,
            'assessments' => $assessments,
            'sessionId' => $sessionId,
            'courseName' => $courseName,
            'className' => $className,
            'sectionId' => $sectionId,
            'displayUserName' => fn ($u) => $this->displayUserName($u),
        ])->setPaper('a4', 'landscape');

        return $pdf->download("gradebook_class{$classId}_course{$courseId}.pdf");
    }
}
