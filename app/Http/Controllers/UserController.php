<?php

namespace App\Http\Controllers;

use App\Models\StudentParentInfo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\UserInterface;
use App\Interfaces\SectionInterface;
use App\Interfaces\SchoolClassInterface;
use App\Repositories\PromotionRepository;
use App\Http\Requests\StudentStoreRequest;
use App\Http\Requests\TeacherStoreRequest;
use App\Interfaces\SchoolSessionInterface;
use App\Repositories\StudentParentInfoRepository;
use App\Models\User;
use App\Models\Role;
use App\Models\Promotion;
use App\Models\AssignedTeacher;
use Illuminate\Support\Facades\Auth;






use Illuminate\Support\Facades\Hash;
class UserController extends Controller
{
    use SchoolSession;
    protected $userRepository;
    protected $schoolSessionRepository;
    protected $schoolClassRepository;
    protected $schoolSectionRepository;

    public function __construct(
        UserInterface $userRepository,
        SchoolSessionInterface $schoolSessionRepository,
        SchoolClassInterface $schoolClassRepository,
        SectionInterface $schoolSectionRepository
    ) {


        $this->userRepository          = $userRepository;
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository   = $schoolClassRepository;
        $this->schoolSectionRepository = $schoolSectionRepository;
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  TeacherStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function storeTeacher(TeacherStoreRequest $request)
    {
        try {
            $this->userRepository->createTeacher($request->validated());

            return back()->with('status', 'Teacher creation was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function getStudentList(Request $request)
    {
        $user = auth()->user();

        // 👨‍👩‍👧‍👦 لو Parent ما بدنا صفحة Student List نهائياً → روح مباشرة إلى My Children
        if ($user->hasRole('parent')) {
            return redirect()->route('parent.children');
        }

        $current_school_session_id = $this->getSchoolCurrentSession();
        $class_id   = $request->query('class_id', 0);
        $section_id = $request->query('section_id', 0);

        try {
            $school_classes = $this->schoolClassRepository->getAllBySession($current_school_session_id);

            // 👨‍🏫 Teacher → فقط طلاب صفوفه
            if ($user->hasRole('teacher')) {
                $teacherClasses = $user->teacherCourses()->pluck('class_id')->toArray();

                $studentList = $this->userRepository
                    ->getAllStudents($current_school_session_id, $class_id, $section_id)
                    ->whereIn('class_id', $teacherClasses);
            }
            // 🧑‍💼 Admin أو أي دور آخر → يشوف كل الطلاب
            else {
                $studentList = $this->userRepository
                    ->getAllStudents($current_school_session_id, $class_id, $section_id);
            }

            $data = [
                'studentList'    => $studentList,
                'school_classes' => $school_classes,
            ];

            return view('students.list', $data);
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    /**
     * صفحة My Children الخاصة بالأهل فقط
     */
    public function getMyChildren()
    {
        $parentId = Auth::id();

        // نجيب صفوف StudentParentInfo مع الطالب نفسه
        $children = StudentParentInfo::with('student')
            ->where('parent_user_id', $parentId)
            ->get();

        return view('parent.children-list', [
            'children' => $children,
        ]);
    }




    public function showStudentProfile($id) {
        $student = $this->userRepository->findStudent($id);

        $current_school_session_id = $this->getSchoolCurrentSession();
        $promotionRepository = new PromotionRepository();
        $promotion_info = $promotionRepository->getPromotionInfoById($current_school_session_id, $id);

        $data = [
            'student'           => $student,
            'promotion_info'    => $promotion_info,
        ];

        return view('students.profile', $data);
    }

    public function showTeacherProfile($id) {
        $teacher = $this->userRepository->findTeacher($id);
        $data = [
            'teacher'   => $teacher,
        ];
        return view('teachers.profile', $data);
    }


    public function createStudent()
    {
        // 1️⃣ احصل على الـ session الحالية أو آخر واحدة موجودة
        $current_school_session_id = $this->getSchoolCurrentSession() ??
            \App\Models\SchoolSession::latest()->value('id');

        // 2️⃣ اجلب الصفوف والشعب بناءً على session_id (أو الكل إذا لم نجد)
        $school_classes = $current_school_session_id
            ? $this->schoolClassRepository->getAllBySession($current_school_session_id)
            : $this->schoolClassRepository->getAll();

        $sections = $current_school_session_id
            ? \App\Models\Section::where('session_id', $current_school_session_id)->get()
            : \App\Models\Section::all();

        // 3️⃣ لو ما في صفوف أو شعب، لا نمنع المستخدم — فقط نظهر رسالة تحذير
        if ($school_classes->isEmpty() || $sections->isEmpty()) {
            session()->flash('warning', '⚠️ لم يتم العثور على صفوف أو شعب مرتبطة بالـ Session الحالية. سيتم عرض كل الصفوف والشعب المتاحة.');
        }

        $data = [
            'current_school_session_id' => $current_school_session_id,
            'school_classes'            => $school_classes,
            'sections'                  => $sections, // 🆕 تمرير الشعب للـ Blade
        ];

        return view('students.add', $data);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  StudentStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function storeStudent(StudentStoreRequest $request)
    {
        try {
            $this->userRepository->createStudent($request->validated());

            return back()->with('status', 'Student creation was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function editStudent($student_id) {
        $student = $this->userRepository->findStudent($student_id);
        $studentParentInfoRepository = new StudentParentInfoRepository();
        $parent_info = $studentParentInfoRepository->getParentInfo($student_id);
        $promotionRepository = new PromotionRepository();
        $current_school_session_id = $this->getSchoolCurrentSession();
        $promotion_info = $promotionRepository->getPromotionInfoById($current_school_session_id, $student_id);

        $data = [
            'student'       => $student,
            'parent_info'   => $parent_info,
            'promotion_info'=> $promotion_info,
        ];
        return view('students.edit', $data);
    }

    public function updateStudent(Request $request) {
        try {
            $this->userRepository->updateStudent($request->toArray());

            return back()->with('status', 'Student update was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function editTeacher($teacher_id) {
        $teacher = $this->userRepository->findTeacher($teacher_id);

        $data = [
            'teacher'   => $teacher,
        ];

        return view('teachers.edit', $data);
    }
    public function updateTeacher(Request $request) {
        try {
            $this->userRepository->updateTeacher($request->toArray());

            return back()->with('status', 'Teacher update was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function getTeacherList()
    {
        $user = auth()->user();

        // 👨‍👩‍👧‍👦 لو المستخدم Parent → يشوف فقط المعلمين اللي بيدرّسوا أولاده
        if ($user->hasRole('parent')) {

            $current_school_session_id = $this->getSchoolCurrentSession();

            // IDs أبناء هذا الأب من جدول student_parent_infos
            $childrenIds = StudentParentInfo::where('parent_user_id', $user->id)
                ->pluck('student_id')
                ->toArray();

            // لو ما عنده أبناء مربوطين → يرجع قائمة فاضية
            if (empty($childrenIds)) {
                $teachers = collect();
            } else {
                // نجيب ترفيعات الأبناء عشان نعرف الصف + الشعبة لكل ابن
                $promotions = Promotion::whereIn('student_id', $childrenIds)
                    ->where('session_id', $current_school_session_id)
                    ->get();

                $classIds   = $promotions->pluck('class_id')->unique()->toArray();
                $sectionIds = $promotions->pluck('section_id')->unique()->toArray();

                if (empty($classIds) || empty($sectionIds)) {
                    $teachers = collect();
                } else {
                    // نجيب المعلمين المعيّنين على هذه الصفوف/الشعب في هذه الـ session
                    $assigned = AssignedTeacher::with('teacher')
                        ->where('session_id', $current_school_session_id)
                        ->whereIn('class_id', $classIds)
                        ->whereIn('section_id', $sectionIds)
                        ->get();

                    // نستخرج الـ teachers فقط مع إزالة التكرار
                    $teachers = $assigned->pluck('teacher')
                        ->filter()
                        ->unique('id')
                        ->values();
                }
            }

        }
        // 👨‍🏫 لو Teacher → خلّيه يشوف باقي المعلمين عادي (أو ممكن نفلتر لاحقاً لو حاب)
        elseif ($user->hasRole('teacher')) {
            $teachers = $this->userRepository->getAllTeachers();
        }
        // 🧑‍💼 لو Admin أو أي دور آخر معه صلاحيات → يشوف كل المعلمين
        else {
            $teachers = $this->userRepository->getAllTeachers();
        }

        $data = [
            'teachers' => $teachers,
        ];

        return view('teachers.list', $data);
    }

    public function getMyChildrenIdsForAuthParent(): array
    {
        // عدّل هذا حسب جدول الربط عندك
        // إذا عندك children في users table (مثل parent_id) استخدمه.
        // مثال شائع:
        return \App\Models\User::where('parent_id', auth()->id())->pluck('id')->toArray();
    }










}
