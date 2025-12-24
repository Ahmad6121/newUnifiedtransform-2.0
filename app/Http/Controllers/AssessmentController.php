<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\SchoolSession;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AssessmentController extends Controller
{
    private function currentSessionId(): int
    {
        if (session()->has('browse_session_id')) return (int) session('browse_session_id');
        return (int) (SchoolSession::latest()->value('id') ?? 0);
    }

    private function authorizeRole(): void
    {
        if (!in_array(auth()->user()->role, ['admin','teacher'])) abort(403);
    }

    private function authorizeOwner(Assessment $assessment): void
    {
        // teacher only edits his own
        if (auth()->user()->role === 'teacher' && (int)$assessment->teacher_id !== (int)auth()->id()) {
            abort(403);
        }
    }

    public function index()
    {
        $this->authorizeRole();

        $sessionId = $this->currentSessionId();

        $q = Assessment::query()->where('session_id', $sessionId);

        // teacher يرى فقط امتحاناته
        if (auth()->user()->role === 'teacher') {
            $q->where('teacher_id', auth()->id());
        }

        $assessments = $q->orderByDesc('id')->paginate(20);

        return view('assessments.index', compact('assessments'));
    }

    public function create(Request $request)
    {
        // ✅ current session id (school_sessions عندك فيها session_name وليس title)
        $sessionId = (int) DB::table('school_sessions')->orderByDesc('id')->value('id');

        // Classes
        $classes = SchoolClass::orderBy('id')->get();

        // selected class_id من query أو أول صف
        $selectedClassId = (int) $request->get('class_id');
        if (!$selectedClassId && $classes->count() > 0) {
            $selectedClassId = (int) $classes->first()->id;
        }

        // ✅ Courses filtered by session + class (هذا هو الحل الحقيقي للتكرار)
        $courses = collect();
        if ($sessionId && $selectedClassId) {
            $courses = Course::where('session_id', $sessionId)
                ->where('class_id', $selectedClassId)
                ->orderBy('course_name')
                ->get();
        }

        return view('assessments.create', [
            'sessionId' => $sessionId,
            'classes' => $classes,
            'selectedClassId' => $selectedClassId,
            'courses' => $courses,
        ]);
    }


    public function store(Request $request)
    {
        // ✅ defaults BEFORE validation (fix kind/status required)
        $mode = $request->input('mode'); // مثال: manual / online
        $request->merge([
            'status' => $request->input('status', 'draft'),
            'kind'   => $request->input('kind', ($mode === 'manual' ? 'MANUAL' : 'ONLINE')),
        ]);

        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'mode'          => 'required|string', // خليها حسب القيم عندك
            'class_id'      => 'required|integer',
            'course_id'     => 'required|integer',
            'section_id'    => 'nullable|integer',

            'total_marks'   => 'required|numeric|min:0',
            'weight_percent'=> 'nullable|numeric|min:0|max:100',
            'passing_marks' => 'nullable|numeric|min:0',

            // ✅ now they exist
            'kind'          => 'required|string|max:50',
            'status'        => 'required|string|max:50',
        ]);

        // ✅ optional: passing_marks cannot exceed total_marks
        if (isset($data['passing_marks']) && $data['passing_marks'] !== null) {
            $data['passing_marks'] = min((float)$data['passing_marks'], (float)$data['total_marks']);
        }

        // ✅ إذا عندك session_id لازم ينحط (حسب مشروعك)
        // مثال: خذ آخر session
        $data['session_id'] = $data['session_id'] ?? \Illuminate\Support\Facades\DB::table('school_sessions')
            ->orderByDesc('id')->value('id');

        \App\Models\Assessment::create($data);

        return redirect()->route('assessments.index')->with('success', 'Assessment created successfully.');
    }


    public function edit(Assessment $assessment)
    {
        $this->authorizeRole();
        $this->authorizeOwner($assessment);

        $sessionId = $this->currentSessionId();

        $classes  = SchoolClass::where('session_id', $sessionId)->orderBy('class_name')->get();
        $courses  = Course::where('session_id', $sessionId)->orderBy('course_name')->get();
        $sections = Section::where('session_id', $sessionId)->orderBy('id')->get();

        return view('assessments.edit', compact('assessment', 'classes', 'courses', 'sections'));
    }

    public function update(Request $request, \App\Models\Assessment $assessment)
    {
        $mode = $request->input('mode', $assessment->mode);
        $request->merge([
            'status' => $request->input('status', $assessment->status ?? 'draft'),
            'kind'   => $request->input('kind', $assessment->kind ?? ($mode === 'manual' ? 'MANUAL' : 'ONLINE')),
        ]);

        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'mode'          => 'required|string',
            'class_id'      => 'required|integer',
            'course_id'     => 'required|integer',
            'section_id'    => 'nullable|integer',

            'total_marks'   => 'required|numeric|min:0',
            'weight_percent'=> 'nullable|numeric|min:0|max:100',
            'passing_marks' => 'nullable|numeric|min:0',

            'kind'          => 'required|string|max:50',
            'status'        => 'required|string|max:50',
        ]);

        if (isset($data['passing_marks']) && $data['passing_marks'] !== null) {
            $data['passing_marks'] = min((float)$data['passing_marks'], (float)$data['total_marks']);
        }

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

    public function publish($id)
    {
        $this->authorizeRole();

        $a = Assessment::findOrFail($id);
        $this->authorizeOwner($a);

        $a->status = 'published';
        $a->published_at = now();
        $a->save();

        return back()->with('success', 'Assessment published successfully.');
    }

    public function close($id)
    {
        $this->authorizeRole();

        $a = Assessment::findOrFail($id);
        $this->authorizeOwner($a);

        $a->status = 'closed';
        $a->closed_at = now();
        $a->save();

        return back()->with('success', 'Assessment closed successfully.');
    }

    public function unpublish(Assessment $assessment)
    {
        $this->authorizeRole();
        $this->authorizeOwner($assessment);

        $assessment->status = 'draft';
        $assessment->published_at = null;
        $assessment->closed_at = null;
        $assessment->save();

        return back()->with('success', 'Assessment moved back to draft.');
    }

    // Publish/Hide results
    public function publishResults($id)
    {
        $this->authorizeRole();

        $a = Assessment::findOrFail($id);
        $this->authorizeOwner($a);

        $a->update(['results_published' => true]);
        return back()->with('success', 'Results published!');
    }

    public function unpublishResults($id)
    {
        $this->authorizeRole();

        $a = Assessment::findOrFail($id);
        $this->authorizeOwner($a);

        $a->update(['results_published' => false]);
        return back()->with('success', 'Results hidden!');
    }
}
