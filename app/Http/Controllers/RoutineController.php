<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoutineStoreRequest;
use App\Models\Routine;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Repositories\RoutineRepository;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SchoolSessionInterface;

class RoutineController extends Controller
{
    use SchoolSession;
    protected $schoolSessionRepository;
    protected $schoolClassRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository, SchoolClassInterface $schoolClassRepository)
    {
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository = $schoolClassRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $current_school_session_id = $this->getSchoolCurrentSession();
        $school_classes = $this->schoolClassRepository->getAllBySession($current_school_session_id);

        $data = [
            'current_school_session_id' => $current_school_session_id,
            'classes'                   => $school_classes,
        ];

        return view('routines.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  RoutineStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RoutineStoreRequest $request)
    {
        try {
            $routineRepository = new RoutineRepository();
            $routineRepository->saveRoutine($request->validated());

            return back()->with('status', 'Routine save was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $routine
     * @return \Illuminate\Http\Response
     */

    public function show(\Illuminate\Http\Request $request)
    {
        $classId   = $request->input('class_id');
        $sectionId = $request->input('section_id');

        $sessionId = session('browse_session_id') ??
            \App\Models\SchoolSession::latest()->value('id');

        $routines = \App\Models\Routine::with([
            'course:id,course_name',
            'teacher:id,first_name,last_name',
            'class:id,class_name',
            'section:id,section_name',
        ])
            ->when($sessionId, function ($q) use ($sessionId) {
                return $q->where('session_id', $sessionId);
            })
            ->when($classId, function ($q) use ($classId) {
                return $q->where('class_id', $classId);
            })
            ->when($sectionId, function ($q) use ($sectionId) {
                return $q->where('section_id', $sectionId);
            })
            ->orderByRaw("FIELD(day,'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday')")
            ->orderBy('start_time')
            ->get();

        $class   = $classId ? \App\Models\SchoolClass::find($classId) : null;
        $section = $sectionId ? \App\Models\Section::find($sectionId) : null;

        // 🆕 ضمان وجود اسم معلم حتى لو teacher_id null
        $routines->transform(function ($routine) {
            $routine->teacher_name = $routine->teacher
                ? $routine->teacher->first_name . ' ' . $routine->teacher->last_name
                : '-';
            return $routine;
        });

        return view('routines.view', compact('routines','class','section'));
    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Routine  $routine
     * @return \Illuminate\Http\Response
     */
    public function edit(Routine $routine)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Routine  $routine
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Routine $routine)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Routine  $routine
     * @return \Illuminate\Http\Response
     */
    public function destroy(Routine $routine)
    {
        //
    }
}
