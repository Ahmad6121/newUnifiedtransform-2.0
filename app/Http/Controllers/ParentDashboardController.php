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
        $this->middleware('auth');

        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->promotionRepository     = $promotionRepository;
        $this->noticeRepository        = $noticeRepository;
    }

    public function index()
    {
        $parent = Auth::user();
        $current_school_session_id = $this->getSchoolCurrentSession();

        $childrenInfos = StudentParentInfo::with('student')
            ->where('parent_user_id', $parent->id)
            ->get();

        $children      = $childrenInfos->pluck('student')->filter()->values();
        $childrenCount = $children->count();
        $activeChild   = $children->first();

        $promotion_info   = null;
        $teachersForChild = collect();
        $teacherCount     = 0;

        if ($activeChild) {
            $promotion_info = $this->promotionRepository
                ->getPromotionInfoById($current_school_session_id, $activeChild->id);

            if ($promotion_info) {
                $assigned = AssignedTeacher::with('teacher')
                    ->where('class_id', $promotion_info->class_id)
                    ->where('section_id', $promotion_info->section_id)
                    ->where('session_id', $current_school_session_id)
                    ->get();

                $teachersForChild = $assigned->pluck('teacher')->filter()->unique('id')->values();
                $teacherCount = $teachersForChild->count();
            }
        }

        // ✅ أهم سطر: فقط اللي مسموح للـ parent يشوفه
        $notices = $this->noticeRepository->getAllVisible($current_school_session_id, $parent);

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

        $children    = $childrenInfos->pluck('student')->filter()->values();
        $activeChild = $children->first();

        return view('parent.progress', [
            'parent'             => $parent,
            'children'           => $children,
            'activeChild'        => $activeChild,
            'current_session_id' => $current_school_session_id,
        ]);
    }
}
