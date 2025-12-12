<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Traits\SchoolSession;
use App\Models\StudentParentInfo;
use App\Models\AssignedTeacher;
use App\Repositories\PromotionRepository;
use App\Repositories\NoticeRepository;
use App\Interfaces\SchoolSessionInterface;

class ParentDashboardController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;
    protected $promotionRepository;
    protected $noticeRepository;

    public function __construct(
        SchoolSessionInterface $schoolSessionRepository,
        PromotionRepository $promotionRepository,
        NoticeRepository $noticeRepository
    ) {
        // الأهل لازم يكونوا مسجلين دخول ومعهم role parent
        $this->middleware('auth');

        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->promotionRepository     = $promotionRepository;
        $this->noticeRepository        = $noticeRepository;
    }

    public function index()
    {
        $parent = Auth::user();

        // 🧩 session الحالية عن طريق الـ Trait
        $current_school_session_id = $this->getSchoolCurrentSession();

        // 🧒 كل الأبناء المرتبطين بهذا الـ parent
        $childrenInfos = StudentParentInfo::with('student')
            ->where('parent_user_id', $parent->id)
            ->get();

        $children      = $childrenInfos->pluck('student')->filter();
        $childrenCount = $children->count();
        $activeChild   = $children->first(); // حالياً أول طفل هو الـ active

        $promotion_info   = null;
        $teachersForChild = collect();
        $teacherCount     = 0;

        if ($activeChild) {
            // معلومات الترفيع للطفل الفعّال (عشان نعرف الصف والشعبة)
            $promotion_info = $this->promotionRepository
                ->getPromotionInfoById($current_school_session_id, $activeChild->id);

            if ($promotion_info) {
                // كل المعلّمين اللي بيدرّسوا هذا الصف + هذه الشعبة
                $assigned = AssignedTeacher::with('teacher')
                    ->where('class_id', $promotion_info->class_id)
                    ->where('section_id', $promotion_info->section_id)
                    ->where('session_id', $current_school_session_id)
                    ->get();

                $teachersForChild = $assigned
                    ->pluck('teacher')
                    ->filter()
                    ->unique('id')
                    ->values();

                $teacherCount = $teachersForChild->count();
            }
        }

        // آخر الإعلانات (نفس اللي في home)
        $notices = $this->noticeRepository->getAll($current_school_session_id);

        return view('parent.dashboard', [
            'parent'             => $parent,
            'children'           => $children,
            'childrenCount'      => $childrenCount,
            'activeChild'        => $activeChild,
            'teachers'           => $teachersForChild,
            'teacherCount'       => $teacherCount,
            'promotion_info'     => $promotion_info,
            'notices'            => $notices,
            'current_session_id' => $current_school_session_id,
        ]);
    }

    public function progress()
    {
        $parent = Auth::user();

        $current_school_session_id = $this->getSchoolCurrentSession();

        $childrenInfos = StudentParentInfo::with('student')
            ->where('parent_user_id', $parent->id)
            ->get();

        $children    = $childrenInfos->pluck('student')->filter();
        $activeChild = $children->first();

        return view('parent.progress', [
            'parent'             => $parent,
            'children'           => $children,
            'activeChild'        => $activeChild,
            'current_session_id' => $current_school_session_id,
        ]);
    }
}
