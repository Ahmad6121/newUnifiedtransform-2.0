<?php

namespace App\Http\Controllers;

use App\Traits\SchoolSession;
use App\Repositories\NoticeRepository;
use App\Http\Requests\NoticeStoreRequest;
use App\Interfaces\SchoolSessionInterface;

class NoticeController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository)
    {
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->middleware('auth');
    }

    public function create()
    {
        $current_school_session_id = $this->getSchoolCurrentSession();
        return view('notices.create', compact('current_school_session_id'));
    }

    public function store(NoticeStoreRequest $request)
    {
        try {
            (new NoticeRepository())->store($request->validated());
            return back()->with('status', 'Creating Notice was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }
}
