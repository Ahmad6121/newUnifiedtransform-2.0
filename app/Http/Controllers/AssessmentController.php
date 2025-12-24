<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\SchoolSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AssessmentController extends Controller
{
    private function currentSessionId(): int
    {
        if (session()->has('browse_session_id')) {
            return (int) session('browse_session_id');
        }

        // عندك العمود session_name مش title
        return (int) (DB::table('school_sessions')->orderByDesc('id')->value('id') ?? 1);
    }

    private function authorizeRole(): void
    {
        if (!in_array(auth()->user()->role, ['admin', 'teacher'])) {
            abort(403);
        }
    }

    private function authorizeOwner(Assessment $assessment): void
    {
        if (auth()->user()->role === 'teacher' && (int)$assessment->teacher_id !== (int)auth()->id()) {
            abort(403);
        }
    }

    public function index()
    {
        $this->authorizeRole();

        $sessionId = $this->currentSessionId();

        $q = Assessment::query()->where('session_id', $sessionId);

        if (auth()->user()->role === 'teacher') {
            $q->where('teacher_id', auth()->id());
        }

        $assessments = $q->orderByDesc('id')->paginate(20);

        return view('assessments.index', compact('assessments', 'sessionId'));
    }

    public function create()
    {
        $this->authorizeRole();

        $sessionId = $this->currentSessionId();

        $classes = DB::table('school_classes')
            ->when(Schema::hasColumn('school_classes', 'session_id'), function ($q) use ($sessionId) {
                $q->where('session_id', $sessionId);
            })
            ->orderBy('id')
            ->get();

        $defaultClassId  = (int) (request()->get('class_id') ?: (DB::table('school_classes')->min('id') ?? 1));
        $selectedClassId = $defaultClassId;

        $sections = DB::table('sections')
            ->when(Schema::hasColumn('sections', 'class_id'), function ($q) use ($defaultClassId) {
                $q->where('class_id', $defaultClassId);
            })
            ->when(Schema::hasColumn('sections', 'session_id'), function ($q) use ($sessionId) {
                $q->where('session_id', $sessionId);
            })
            ->orderBy('id')
            ->get();

        $courses = DB::table('courses')
            ->where('class_id', $defaultClassId)
            ->when(Schema::hasColumn('courses', 'session_id'), function ($q) use ($sessionId) {
                $q->where('session_id', $sessionId);
            })
            ->orderBy('course_name')
            ->get();

        return view('assessments.create', compact(
            'classes',
            'sections',
            'courses',
            'defaultClassId',
            'selectedClassId',
            'sessionId'
        ));
    }

    public function store(Request $request)
    {
        $this->authorizeRole();

        $mode = $request->input('mode', 'manual');

        // defaults عشان kind/status ما يطلع required error
        $request->merge([
            'status' => $request->input('status', 'draft'),
            'kind'   => $request->input('kind', ($mode === 'manual' ? 'MANUAL' : 'ONLINE')),
        ]);

        $data = $request->validate([
            'session_id'      => 'required|integer|exists:school_sessions,id',
            'title'           => 'required|string|max:255',
            'mode'            => 'required|in:manual,online',
            'class_id'        => 'required|integer|exists:school_classes,id',
            'course_id'       => 'required|integer|exists:courses,id',
            'section_id'      => 'nullable|integer|exists:sections,id',

            'total_marks'     => 'required|numeric|min:0',
            'weight_percent'  => 'required|numeric|min:0|max:100',
            'passing_marks'   => 'nullable|numeric|min:0',

            // ✅ رجّعناهم
            'start_date'         => 'nullable|date',
            'end_date'           => 'nullable|date|after_or_equal:start_date',
            'duration_minutes'   => 'nullable|integer|min:1|max:10000',
            'attempts_allowed'   => 'nullable|integer|min:1|max:1000',
            'randomize_questions'=> 'nullable|boolean',

            'kind'            => 'required|string|max:50',
            'status'          => 'required|string|max:50',
        ]);

        // passing <= total
        if ($data['passing_marks'] !== null && (float)$data['passing_marks'] > (float)$data['total_marks']) {
            $data['passing_marks'] = (float)$data['total_marks'];
        }

        $data['teacher_id'] = auth()->id();

        // ✅ أهم حركة: مرّر فقط الأعمدة الموجودة فعليًا بجدول assessments
        $assessmentCols = Schema::getColumnListing('assessments');
        $data = array_intersect_key($data, array_flip($assessmentCols));

        Assessment::create($data);

        return redirect()->route('assessments.index')->with('success', 'Assessment created successfully.');
    }


    public function edit(Assessment $assessment)
    {
        $this->authorizeRole();
        $this->authorizeOwner($assessment);

        $sessionId = $this->currentSessionId();

        $classes = DB::table('school_classes')
            ->when(Schema::hasColumn('school_classes', 'session_id'), function ($q) use ($sessionId) {
                $q->where('session_id', $sessionId);
            })
            ->orderBy('id')->get();

        $courses = DB::table('courses')
            ->when(Schema::hasColumn('courses', 'session_id'), function ($q) use ($sessionId) {
                $q->where('session_id', $sessionId);
            })
            ->orderBy('course_name')->get();

        $sections = DB::table('sections')
            ->when(Schema::hasColumn('sections', 'session_id'), function ($q) use ($sessionId) {
                $q->where('session_id', $sessionId);
            })
            ->orderBy('id')->get();

        return view('assessments.edit', compact('assessment', 'classes', 'courses', 'sections', 'sessionId'));
    }

    public function update(Request $request, Assessment $assessment)
    {
        $this->authorizeRole();
        $this->authorizeOwner($assessment);

        $mode = $request->input('mode', $assessment->mode);

        $request->merge([
            'status' => $request->input('status', $assessment->status ?? 'draft'),
            'kind'   => $request->input('kind', $assessment->kind ?? ($mode === 'manual' ? 'MANUAL' : 'ONLINE')),
        ]);

        $data = $request->validate([
            'session_id'      => 'required|integer|exists:school_sessions,id',
            'title'           => 'required|string|max:255',
            'mode'            => 'required|in:manual,online',
            'class_id'        => 'required|integer|exists:school_classes,id',
            'course_id'       => 'required|integer|exists:courses,id',
            'section_id'      => 'nullable|integer|exists:sections,id',

            'total_marks'     => 'required|numeric|min:0',
            'weight_percent'  => 'required|numeric|min:0|max:100',
            'passing_marks'   => 'nullable|numeric|min:0',

            // ✅ رجّعناهم
            'start_date'         => 'nullable|date',
            'end_date'           => 'nullable|date|after_or_equal:start_date',
            'duration_minutes'   => 'nullable|integer|min:1|max:10000',
            'attempts_allowed'   => 'nullable|integer|min:1|max:1000',
            'randomize_questions'=> 'nullable|boolean',

            'kind'            => 'required|string|max:50',
            'status'          => 'required|string|max:50',
        ]);

        if ($data['passing_marks'] !== null && (float)$data['passing_marks'] > (float)$data['total_marks']) {
            $data['passing_marks'] = (float)$data['total_marks'];
        }

        $assessmentCols = Schema::getColumnListing('assessments');
        $data = array_intersect_key($data, array_flip($assessmentCols));

        $assessment->update($data);

        return redirect()->route('assessments.index')->with('success', 'Assessment updated successfully.');
    }

    public function destroy(Assessment $assessment)
    {
        $this->authorizeRole();
        $this->authorizeOwner($assessment);

        $assessment->delete();

        return redirect()->route('assessments.index')->with('success', 'Assessment deleted successfully.');
    }
    // ✅ Publish assessment
    public function publish(Assessment $assessment)
    {
        $this->authorizeRole();
        $this->authorizeOwner($assessment);

        // status
        if (\Illuminate\Support\Facades\Schema::hasColumn('assessments', 'status')) {
            $assessment->status = 'published';
        }

        // published_at (اختياري)
        if (\Illuminate\Support\Facades\Schema::hasColumn('assessments', 'published_at')) {
            $assessment->published_at = now();
        }

        $assessment->save();

        return back()->with('success', 'Assessment published successfully.');
    }

// ✅ Close assessment
    public function close(Assessment $assessment)
    {
        $this->authorizeRole();
        $this->authorizeOwner($assessment);

        if (\Illuminate\Support\Facades\Schema::hasColumn('assessments', 'status')) {
            $assessment->status = 'closed';
        }

        // closed_at (اختياري)
        if (\Illuminate\Support\Facades\Schema::hasColumn('assessments', 'closed_at')) {
            $assessment->closed_at = now();
        }

        $assessment->save();

        return back()->with('success', 'Assessment closed successfully.');
    }

// ✅ Unpublish assessment (back to draft)
    public function unpublish(Assessment $assessment)
    {
        $this->authorizeRole();
        $this->authorizeOwner($assessment);

        if (\Illuminate\Support\Facades\Schema::hasColumn('assessments', 'status')) {
            $assessment->status = 'draft';
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('assessments', 'published_at')) {
            $assessment->published_at = null;
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('assessments', 'closed_at')) {
            $assessment->closed_at = null;
        }

        $assessment->save();

        return back()->with('success', 'Assessment moved back to draft.');
    }

    // ✅ Publish results
    public function publishResults(Assessment $assessment)
    {
        $this->authorizeRole();
        $this->authorizeOwner($assessment);

        // إذا العمود موجود
        if (\Illuminate\Support\Facades\Schema::hasColumn('assessments', 'results_published')) {
            $assessment->results_published = 1;
        }

        $assessment->save();

        return back()->with('success', 'Results published successfully.');
    }

// ✅ Hide results
    public function unpublishResults(Assessment $assessment)
    {
        $this->authorizeRole();
        $this->authorizeOwner($assessment);

        if (\Illuminate\Support\Facades\Schema::hasColumn('assessments', 'results_published')) {
            $assessment->results_published = 0;
        }

        $assessment->save();

        return back()->with('success', 'Results hidden successfully.');
    }


}
