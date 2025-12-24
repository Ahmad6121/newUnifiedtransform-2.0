<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class ReportsPageController extends Controller
{
    private function authorizeReports(): void
    {
        if (!in_array(auth()->user()->role, ['admin', 'teacher'])) abort(403);
    }

    public function exports()
    {
        $this->authorizeReports();

        $session = DB::table('school_sessions')->orderByDesc('id')->first();
        $sessionId = $session ? (int)$session->id : null;

        $classes = DB::table('school_classes')->orderBy('id')->get();
        $classId = $classes->first() ? (int)$classes->first()->id : null;

        $sections = DB::table('sections')
            ->when($classId, function ($q) use ($classId) {
                // أغلب المشاريع sections فيها class_id
                if (DB::getSchemaBuilder()->hasColumn('sections', 'class_id')) {
                    $q->where('class_id', $classId);
                }
            })
            ->orderBy('id')
            ->get();

        $courses = DB::table('courses')
            ->when($sessionId, function ($q) use ($sessionId) { $q->where('session_id', $sessionId); })
            ->when($classId, function ($q) use ($classId) { $q->where('class_id', $classId); })
            ->orderBy('id')
            ->get();

        return view('reports.exports', compact('sessionId', 'classes', 'sections', 'courses'));
    }
}
