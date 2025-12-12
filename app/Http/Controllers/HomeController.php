<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\SchoolSession;
use App\Interfaces\UserInterface;
use App\Repositories\NoticeRepository;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Repositories\PromotionRepository;

class HomeController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;
    protected $schoolClassRepository;
    protected $userRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        UserInterface $userRepository,
        SchoolSessionInterface $schoolSessionRepository,
        SchoolClassInterface $schoolClassRepository
    ) {
        // $this->middleware('auth');
        $this->userRepository = $userRepository;
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository = $schoolClassRepository;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // ✅ لو المستخدم مسجل دخول وهو Parent → حوله على داشبورد الأهل
        $user = auth()->user();

        if ($user && $user->hasRole('parent')) {
            return redirect()->route('parent.dashboard');
        }

        // 🟢 باقي الأدوار (أدمِن، طالب، معلم) يشوفوا داشبورد النظام العادي
        $current_school_session_id = $this->getSchoolCurrentSession();

        $classCount = $this->schoolClassRepository
            ->getAllBySession($current_school_session_id)
            ->count();

        $studentCount = $this->userRepository
            ->getAllStudentsBySessionCount($current_school_session_id);

        $promotionRepository = new PromotionRepository();
        $maleStudentsBySession = $promotionRepository
            ->getMaleStudentsBySessionCount($current_school_session_id);

        $teacherCount = $this->userRepository
            ->getAllTeachers()
            ->count();

        $noticeRepository = new NoticeRepository();
        $notices = $noticeRepository->getAll($current_school_session_id);

        $data = [
            'classCount'            => $classCount,
            'studentCount'          => $studentCount,
            'teacherCount'          => $teacherCount,
            'notices'               => $notices,
            'maleStudentsBySession' => $maleStudentsBySession,
        ];

        return view('home', $data);
    }
}
