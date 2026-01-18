<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Interfaces\SchoolSessionInterface;
use App\Traits\SchoolSession as SchoolSessionTrait;

class AttendanceController extends Controller
{
    use SchoolSessionTrait;

    /** @var SchoolSessionInterface */
    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository)
    {
        $this->middleware('auth');
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    // =========================
    // Pages
    // =========================

    public function showStudentAttendance(int $studentId)
    {
        $u = Auth::user();
        abort_unless($u, 403);

        // ✅ صلاحيات: Admin/Teacher أو الطالب نفسه
        $isAdmin   = $this->isAdmin($u);
        $isTeacher = $this->isTeacher($u);
        $isStudentSelf = ((int)$u->id === (int)$studentId);

        abort_unless($isAdmin || $isTeacher || $isStudentSelf, 403);

        abort_unless(Schema::hasTable('users'), 500);

        $student = DB::table('users')->where('id', $studentId)->first();
        abort_unless($student, 404);

        $sessionId = (int) $this->getSchoolCurrentSession();

        // ✅ جلب كل حضور الطالب (مع اسم الكورس/الشعبة إذا موجود)
        $q = DB::table('attendances as a')
            ->where('a.student_id', $studentId);

        if (Schema::hasColumn('attendances', 'session_id')) {
            $q->where('a.session_id', $sessionId);
        }

        // joins اختيارية (لو موجودة بالجداول)
        if (Schema::hasTable('courses') && Schema::hasColumn('attendances', 'course_id')) {
            $q->leftJoin('courses as c', 'c.id', '=', 'a.course_id');
            $q->addSelect('c.course_name as course_name');
        } else {
            $q->addSelect(DB::raw("'' as course_name"));
        }

        if (Schema::hasTable('school_sections') && Schema::hasColumn('attendances', 'section_id')) {
            $q->leftJoin('school_sections as s', 's.id', '=', 'a.section_id');
            $q->addSelect('s.section_name as section_name');
        } else {
            $q->addSelect(DB::raw("'' as section_name"));
        }

        $attendances = $q->select('a.*')
            ->orderByDesc('a.created_at')
            ->get()
            ->map(function ($r) {
                // ✅ نفس منطق الفيو تبعتك: section == null ? course : section
                $r->context = !empty($r->section_name) ? $r->section_name : ($r->course_name ?? '');
                return $r;
            });

        return view('attendances.student-view', [
            'student' => $student,
            'attendances' => $attendances,
        ]);
    }


    public function index(Request $request)
    {
        $u = Auth::user();
        abort_unless($u, 403);
        abort_unless($this->isAdmin($u) || $this->isTeacher($u), 403);

        $sessionId = (int) $this->getSchoolCurrentSession();
        $academic_setting = $this->getAcademicSetting();

        // (نتركه كما هو للقائمة العامة)
        [$classes, $sections, $courses] = $this->buildOptionsForUser(
            $u,
            $sessionId,
            (string)($academic_setting->attendance_type ?? 'section')
        );

        /**
         * ✅ حل التكرار:
         * نجمع الشعب حسب الصف (class_id) مع دعم أسماء أعمدة مختلفة
         * وبنفس الوقت نعمل unique('id') لإزالة أي تكرار
         */
        $sectionsByClass = collect($sections)
            ->filter(function ($s) {
                // تجاهل أي صفوف/شعب ناقصة id
                return isset($s->id);
            })
            ->groupBy(function ($s) {
                // يدعم أكثر من اسم عمود محتمل
                if (isset($s->class_id)) return (int) $s->class_id;
                if (isset($s->school_class_id)) return (int) $s->school_class_id;
                if (isset($s->classId)) return (int) $s->classId;
                return 0;
            })
            ->map(function ($group) {
                return $group->unique('id')->values();
            });

        return view('attendances.index', [
            'classes_and_sections' => [
                'school_classes'  => $classes,
                'school_sections' => $sections, // نخليه موجود لو بدك تستخدمه لأي شيء
            ],

            // ✅ هذا المهم للـ Blade حتى يعرض شعب الصف الصحيح بدون تكرار
            'sectionsByClass' => $sectionsByClass,

            'courses' => $courses,
            'academic_setting' => $academic_setting,
            'current_school_session_id' => $sessionId,
        ]);
    }


    public function show(Request $request)
    {
        $u = Auth::user();
        abort_unless($u, 403);
        abort_unless($this->isAdmin($u) || $this->isTeacher($u), 403);

        $sessionId = (int) $this->getSchoolCurrentSession();
        $academic_setting = $this->getAcademicSetting();

        $classId   = (int) $request->query('class_id', 0);
        $sectionId = (int) $request->query('section_id', 0);

        // نحن نعتمد section فقط حسب طلبك
        $type = 'section';


        $attendances = $this->getTodayAttendances($sessionId, $type, $classId, $sectionId, 0);

        return view('attendances.view', [
            'attendances' => $attendances,
            'academic_setting' => $academic_setting,
            'current_school_session_id' => $sessionId,
        ]);
    }

    /**
     * Take Attendance
     * Flow: Class -> Section -> Students
     */
    public function create(Request $request)
    {
        $u = Auth::user();
        abort_unless($u, 403);
        abort_unless($this->isAdmin($u) || $this->isTeacher($u), 403);

        $sessionId = (int) $this->getSchoolCurrentSession();
        $academic_setting = $this->getAcademicSetting();

        $attendanceType = 'section';

        // classes list (admin=all, teacher=allowed; fallback from promotions if assignments empty)
        $classes = $this->getClassesForTakeAttendance($u, $sessionId);

        $classId   = (int) $request->query('class_id', 0);
        $sectionId = (int) $request->query('section_id', 0);

        // sections for selected class
        $sections = $this->getSectionsForTakeAttendance($u, $sessionId, $classId);

        // students
        $student_list = collect();
        $attendance_count = 0;

        if ($classId > 0 && $sectionId > 0) {
            $this->ensureTeacherCanAccessSelection($u, $sessionId, 'section', $classId, $sectionId, 0);

            $students = $this->getStudentsForClassSection($sessionId, $classId, $sectionId);

            $student_list = $students->map(function ($s) {
                return (object)[
                    'student_id'     => (int)($s->id ?? $s->student_id ?? 0),
                    'first_name'     => (string)($s->first_name ?? ''),
                    'last_name'      => (string)($s->last_name ?? ''),
                    'id_card_number' => (string)($s->id_card_number ?? ''),
                ];
            })->filter(function ($x) {
                return $x->student_id > 0;
            })->values();

            $attendance_count = $this->countTodayTaken($sessionId, 'section', $classId, $sectionId, 0);
        }

        $selectedClass   = $classes->firstWhere('id', $classId);
        $selectedSection = $sections->firstWhere('id', $sectionId);

        return view('attendances.take', [
            'academic_setting' => $academic_setting,
            'current_school_session_id' => $sessionId,

            'classes' => $classes,
            'sections' => $sections,

            'classId' => $classId,
            'sectionId' => $sectionId,

            'className' => $selectedClass->class_name ?? '',
            'sectionName' => $selectedSection->section_name ?? '',

            'student_list' => $student_list,
            'attendance_count' => $attendance_count,
        ]);
    }

    public function store(Request $request)
    {
        $u = Auth::user();
        abort_unless($u, 403);
        abort_unless($this->isAdmin($u) || $this->isTeacher($u), 403);

        $sessionId = (int) $request->input('session_id', $this->getSchoolCurrentSession());
        $classId   = (int) $request->input('class_id', 0);
        $sectionId = (int) $request->input('section_id', 0);

        $type = 'section';

        $this->ensureTeacherCanAccessSelection($u, $sessionId, $type, $classId, $sectionId, 0);

        $studentIds = (array) $request->input('student_ids', []);
        $statusMap  = (array) $request->input('status', []); // status[student_id] => on/off

        abort_unless(Schema::hasTable('attendances'), 500);

        // prevent duplicates today
        $already = $this->countTodayTaken($sessionId, $type, $classId, $sectionId, 0);
        if ($already > 0) {
            return back()->with('status', 'Attendance already taken for today ✅');
        }

        $attTable = 'attendances';
        $attCols  = Schema::getColumnListing($attTable);

        $studentCol = $this->pickColumn($attTable, ['student_id', 'user_id', 'student', 'student_user_id']) ?? 'student_id';

        $now = now();
        $rows = [];

        foreach ($studentIds as $sid) {
            $sid = (int) $sid;
            if ($sid <= 0) continue;

            $val = $statusMap[$sid] ?? 'off';
            $present = ($val === 'on');

            $row = [
                'session_id' => $sessionId,
                'class_id'   => $classId,
                'section_id' => $sectionId,
                'course_id'  => 0,

                $studentCol  => $sid,
                'status'     => $present ? 'on' : 'off',

                'created_at' => $now,
                'updated_at' => $now,
            ];

            // keep only existing columns
            $row = array_intersect_key($row, array_flip($attCols));
            $rows[] = $row;
        }

        if (!empty($rows)) {
            DB::table($attTable)->insert($rows);
        }

        return back()->with('status', 'Attendance saved ✅');
    }

    // =========================
    // Take Attendance Options (Smart)
    // =========================

    private function getClassesForTakeAttendance($u, int $sessionId)
    {
        // Admin => all
        if ($this->isAdmin($u)) {
            return $this->getAllClasses($sessionId);
        }

        // Teacher => from assignments; fallback => promotions
        $pairs = $this->teacherAllowedPairs((int)$u->id, $sessionId, 'section');

        $classIds = collect($pairs)->pluck('class_id')->filter()->unique()->values()->all();

        // fallback if no assignments found
        if (empty($classIds)) {
            $classIds = $this->classIdsFromPromotions($sessionId);
        }

        return $this->getClassesByIds($classIds, $sessionId);
    }

    private function getSectionsForTakeAttendance($u, int $sessionId, int $classId)
    {
        if ($classId <= 0) return collect();

        $sectionsTable = $this->sectionsTable();
        if (!$sectionsTable) return collect();

        // Admin: sections by class if possible, else all
        if ($this->isAdmin($u)) {
            $q = DB::table($sectionsTable)->orderBy('id', 'asc');

            $classCol = $this->pickColumn($sectionsTable, ['class_id', 'school_class_id']);
            if ($classCol) $q->where($classCol, $classId);

            if ($sessionId && Schema::hasColumn($sectionsTable, 'session_id')) $q->where('session_id', $sessionId);

            return $q->get();
        }

        // Teacher: sections from assignments; fallback => promotions for that class
        $pairs = $this->teacherAllowedPairs((int)$u->id, $sessionId, 'section');

        $sectionIds = collect($pairs)
            ->filter(function ($p) use ($classId) {
                return (int)($p['class_id'] ?? 0) === $classId;
            })
            ->pluck('section_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($sectionIds)) {
            $sectionIds = $this->sectionIdsForClassFromPromotions($sessionId, $classId);
        }

        return $this->getSectionsByIds($sectionIds, $sessionId);
    }

    // =========================
    // Academic Setting
    // =========================

    private function getAcademicSetting()
    {
        $obj = (object)['attendance_type' => 'section'];

        if (Schema::hasTable('academic_settings')) {
            $row = DB::table('academic_settings')->orderByDesc('id')->first();
            if ($row && isset($row->attendance_type)) return $row;
        }

        if (class_exists(\App\Models\AcademicSetting::class)) {
            $m = \App\Models\AcademicSetting::query()->first();
            if ($m) return $m;
        }

        return $obj;
    }

    // =========================
    // Generic Options (used by index page)
    // =========================

    private function buildOptionsForUser($u, int $sessionId, string $attendanceType): array
    {
        if ($this->isAdmin($u)) {
            return [
                $this->getAllClasses($sessionId),
                $this->getAllSections($sessionId),
                $this->getAllCourses($sessionId),
            ];
        }

        $pairs = $this->teacherAllowedPairs((int)$u->id, $sessionId, $attendanceType);

        $classIds = collect($pairs)->pluck('class_id')->filter()->unique()->values()->all();
        $sectionIds = collect($pairs)->pluck('section_id')->filter()->unique()->values()->all();
        $courseIds = collect($pairs)->pluck('course_id')->filter()->unique()->values()->all();

        return [
            $this->getClassesByIds($classIds, $sessionId),
            $this->getSectionsByIds($sectionIds, $sessionId),
            $this->getCoursesByIds($courseIds, $sessionId),
        ];
    }

    // =========================
    // Teacher Allowed Pairs (SMART columns)
    // =========================

    private function teacherAllowedPairs(int $teacherId, int $sessionId, string $attendanceType): array
    {
        $pairs = [];

        // ---- routines ----
        if (Schema::hasTable('routines')) {
            $t = 'routines';
            $teacherCol = $this->pickColumn($t, ['teacher_id', 'user_id', 'staff_id']);
            $classCol   = $this->pickColumn($t, ['class_id', 'school_class_id']);
            $sectionCol = $this->pickColumn($t, ['section_id', 'school_section_id']);
            $sessionCol = $this->pickColumn($t, ['session_id', 'school_session_id']);

            if ($teacherCol && $classCol && $sectionCol) {
                $q = DB::table($t)->where($teacherCol, $teacherId);
                if ($sessionId && $sessionCol) $q->where($sessionCol, $sessionId);

                $rows = $q->select([$classCol . ' as class_id', $sectionCol . ' as section_id'])->distinct()->get();
                foreach ($rows as $r) {
                    $pairs[] = ['class_id' => (int)$r->class_id, 'section_id' => (int)$r->section_id, 'course_id' => 0];
                }
            }
        }

        // ---- courses.teacher_id ----
        if (Schema::hasTable('courses')) {
            $t = 'courses';
            $teacherCol = $this->pickColumn($t, ['teacher_id', 'user_id', 'staff_id']);
            $classCol   = $this->pickColumn($t, ['class_id', 'school_class_id']);
            $sectionCol = $this->pickColumn($t, ['section_id', 'school_section_id']);
            $sessionCol = $this->pickColumn($t, ['session_id', 'school_session_id']);

            if ($teacherCol) {
                $q = DB::table($t)->where($teacherCol, $teacherId);
                if ($sessionId && $sessionCol) $q->where($sessionCol, $sessionId);

                $select = ['id as course_id'];
                if ($classCol) $select[] = $classCol . ' as class_id';
                if ($sectionCol) $select[] = $sectionCol . ' as section_id';

                $rows = $q->select($select)->get();
                foreach ($rows as $r) {
                    $pairs[] = [
                        'class_id' => (int)($r->class_id ?? 0),
                        'section_id' => (int)($r->section_id ?? 0),
                        'course_id' => (int)($r->course_id ?? 0),
                    ];
                }
            }
        }

        // ---- course_teacher pivot ----
        if (Schema::hasTable('course_teacher') && Schema::hasTable('courses')) {
            $p = 'course_teacher';
            $t = 'courses';

            $tcol = $this->pickColumn($p, ['teacher_id', 'user_id', 'staff_id']);
            $ccol = $this->pickColumn($p, ['course_id']);

            $classCol   = $this->pickColumn($t, ['class_id', 'school_class_id']);
            $sectionCol = $this->pickColumn($t, ['section_id', 'school_section_id']);
            $sessionCol = $this->pickColumn($t, ['session_id', 'school_session_id']);

            if ($tcol && $ccol) {
                $q = DB::table($p . ' as ct')
                    ->join($t . ' as c', 'c.id', '=', 'ct.' . $ccol)
                    ->where('ct.' . $tcol, $teacherId);

                if ($sessionId && $sessionCol) $q->where('c.' . $sessionCol, $sessionId);

                $select = ['c.id as course_id'];
                if ($classCol) $select[] = 'c.' . $classCol . ' as class_id';
                if ($sectionCol) $select[] = 'c.' . $sectionCol . ' as section_id';

                $rows = $q->select($select)->distinct()->get();
                foreach ($rows as $r) {
                    $pairs[] = [
                        'class_id' => (int)($r->class_id ?? 0),
                        'section_id' => (int)($r->section_id ?? 0),
                        'course_id' => (int)($r->course_id ?? 0),
                    ];
                }
            }
        }

        // unique
        $uniq = [];
        foreach ($pairs as $p) {
            $k = ($p['class_id'] ?? 0) . '|' . ($p['section_id'] ?? 0) . '|' . ($p['course_id'] ?? 0);
            $uniq[$k] = $p;
        }

        return array_values($uniq);
    }

    private function ensureTeacherCanAccessSelection($u, int $sessionId, string $type, int $classId, int $sectionId, int $courseId): void
    {
        if ($this->isAdmin($u)) return;
        if (!$this->isTeacher($u)) abort(403);

        $pairs = $this->teacherAllowedPairs((int)$u->id, $sessionId, $type);

        // ✅ fallback: لو ما في assignments مسجلة أصلاً، اسمح واعتمد promotions
        if (empty($pairs)) {
            return;
        }

        $allowed = collect($pairs)->contains(function ($p) use ($classId, $sectionId) {
            return (int)($p['class_id'] ?? 0) === $classId && (int)($p['section_id'] ?? 0) === $sectionId;
        });

        abort_unless($allowed, 403);
    }

    // =========================
    // Classes / Sections / Courses (SMART tables)
    // =========================

    private function classesTable(): ?string
    {
        return $this->pickTable(['school_classes', 'classes']);
    }

    private function sectionsTable(): ?string
    {
        return $this->pickTable(['school_sections', 'sections']);
    }

    private function getAllClasses(int $sessionId)
    {
        $t = $this->classesTable();
        if (!$t) return collect();

        $q = DB::table($t)->orderBy('id', 'asc');
        if ($sessionId && Schema::hasColumn($t, 'session_id')) $q->where('session_id', $sessionId);
        return $q->get();
    }

    private function getAllSections(int $sessionId)
    {
        $t = $this->sectionsTable();
        if (!$t) return collect();

        $q = DB::table($t)->orderBy('id', 'asc');
        if ($sessionId && Schema::hasColumn($t, 'session_id')) $q->where('session_id', $sessionId);
        return $q->get();
    }

    private function getAllCourses(int $sessionId)
    {
        if (!Schema::hasTable('courses')) return collect();

        $q = DB::table('courses')->orderBy('id', 'asc');
        if ($sessionId && Schema::hasColumn('courses', 'session_id')) $q->where('session_id', $sessionId);
        return $q->get();
    }

    private function getClassesByIds(array $ids, int $sessionId)
    {
        $t = $this->classesTable();
        if (!$t) return collect();
        if (empty($ids)) return collect();

        $q = DB::table($t)->whereIn('id', $ids)->orderBy('id', 'asc');
        if ($sessionId && Schema::hasColumn($t, 'session_id')) $q->where('session_id', $sessionId);
        return $q->get();
    }

    private function getSectionsByIds(array $ids, int $sessionId)
    {
        $t = $this->sectionsTable();
        if (!$t) return collect();
        if (empty($ids)) return collect();

        $q = DB::table($t)->whereIn('id', $ids)->orderBy('id', 'asc');
        if ($sessionId && Schema::hasColumn($t, 'session_id')) $q->where('session_id', $sessionId);
        return $q->get();
    }

    private function getCoursesByIds(array $ids, int $sessionId)
    {
        if (!Schema::hasTable('courses')) return collect();
        if (empty($ids)) return collect();

        $q = DB::table('courses')->whereIn('id', $ids)->orderBy('id', 'asc');
        if ($sessionId && Schema::hasColumn('courses', 'session_id')) $q->where('session_id', $sessionId);
        return $q->get();
    }

    // =========================
    // Students from Promotions (SMART columns)
    // =========================

    private function getStudentsForClassSection(int $sessionId, int $classId, int $sectionId)
    {
        $pTable = $this->pickTable(['promotions', 'student_promotions']);
        if (!$pTable || !Schema::hasTable('users')) return collect();

        $studentCol = $this->pickColumn($pTable, ['student_id', 'user_id']) ?? 'student_id';
        $classCol   = $this->pickColumn($pTable, ['class_id', 'school_class_id']) ?? 'class_id';
        $sectionCol = $this->pickColumn($pTable, ['section_id', 'school_section_id']) ?? 'section_id';
        $sessionCol = $this->pickColumn($pTable, ['session_id', 'school_session_id']);

        if (!Schema::hasColumn($pTable, $classCol) || !Schema::hasColumn($pTable, $sectionCol) || !Schema::hasColumn($pTable, $studentCol)) {
            return collect();
        }

        $q = DB::table($pTable . ' as p')
            ->join('users as u', 'u.id', '=', 'p.' . $studentCol)
            ->where('p.' . $classCol, $classId)
            ->where('p.' . $sectionCol, $sectionId);

        if ($sessionId && $sessionCol) {
            $q->where('p.' . $sessionCol, $sessionId);
        }

        // id_card_number
        if (Schema::hasTable('student_academic_infos')) {
            $cols = Schema::getColumnListing('student_academic_infos');
            $sidCol = in_array('student_id', $cols) ? 'student_id' : (in_array('user_id', $cols) ? 'user_id' : null);

            if ($sidCol) {
                $q->leftJoin('student_academic_infos as sai', 'sai.' . $sidCol, '=', 'u.id');
                if (Schema::hasColumn('student_academic_infos', 'id_card_number')) {
                    $q->addSelect('sai.id_card_number');
                }
            }
        }

        return $q->select('u.id', 'u.first_name', 'u.last_name')
            ->distinct()
            ->orderBy('u.first_name')
            ->orderBy('u.last_name')
            ->get();
    }

    // =========================
    // Promotions fallback ids
    // =========================

    private function sectionIdsForClassFromPromotions(int $sessionId, int $classId): array
    {
        $pTable = $this->pickTable(['promotions', 'student_promotions']);
        if (!$pTable) return [];

        $classCol   = $this->pickColumn($pTable, ['class_id', 'school_class_id']) ?? 'class_id';
        $sectionCol = $this->pickColumn($pTable, ['section_id', 'school_section_id']) ?? 'section_id';
        $sessionCol = $this->pickColumn($pTable, ['session_id', 'school_session_id']);

        if (!Schema::hasColumn($pTable, $classCol) || !Schema::hasColumn($pTable, $sectionCol)) return [];

        $q = DB::table($pTable)->where($classCol, $classId);
        if ($sessionId && $sessionCol) $q->where($sessionCol, $sessionId);

        return $q->pluck($sectionCol)->filter()->unique()->values()->all();
    }

    private function classIdsFromPromotions(int $sessionId): array
    {
        $pTable = $this->pickTable(['promotions', 'student_promotions']);
        if (!$pTable) return [];

        $classCol   = $this->pickColumn($pTable, ['class_id', 'school_class_id']) ?? 'class_id';
        $sessionCol = $this->pickColumn($pTable, ['session_id', 'school_session_id']);

        if (!Schema::hasColumn($pTable, $classCol)) return [];

        $q = DB::table($pTable);
        if ($sessionId && $sessionCol) $q->where($sessionCol, $sessionId);

        return $q->pluck($classCol)->filter()->unique()->values()->all();
    }

    // =========================
    // Attendance today helpers (SMART columns)
    // =========================

    private function countTodayTaken(int $sessionId, string $type, int $classId, int $sectionId, int $courseId): int
    {
        if (!Schema::hasTable('attendances')) return 0;

        $t = 'attendances';
        $sessionCol = $this->pickColumn($t, ['session_id', 'school_session_id']);
        $classCol   = $this->pickColumn($t, ['class_id', 'school_class_id']) ?? 'class_id';
        $sectionCol = $this->pickColumn($t, ['section_id', 'school_section_id']) ?? 'section_id';

        $q = DB::table($t);

        if ($sessionCol) $q->where($sessionCol, $sessionId);
        if (Schema::hasColumn($t, $classCol)) $q->where($classCol, $classId);
        if (Schema::hasColumn($t, $sectionCol)) $q->where($sectionCol, $sectionId);

        $q->whereDate('created_at', now()->toDateString());

        return (int) $q->count();
    }

    private function getTodayAttendances(int $sessionId, string $type, int $classId, int $sectionId, int $courseId)
    {
        // ✅ الأفضل: Eloquent (حتى يشتغل $attendance->student في الفيو)
        if (class_exists(\App\Models\Attendance::class)) {

            $q = \App\Models\Attendance::query()
                ->with(['student']) // ✅ لازم علاقة student تكون موجودة بالموديل
                ->whereDate('created_at', now()->toDateString());

            if (Schema::hasColumn('attendances', 'session_id')) $q->where('session_id', $sessionId);
            if (Schema::hasColumn('attendances', 'class_id'))   $q->where('class_id', $classId);
            if (Schema::hasColumn('attendances', 'section_id')) $q->where('section_id', $sectionId);
            if (Schema::hasColumn('attendances', 'course_id'))  $q->where('course_id', $courseId);

            return $q->orderBy('student_id')->get();
        }

        // fallback: DB
        return collect();
    }

    // =========================
    // Role helpers
    // =========================

    private function isAdmin($u): bool
    {
        if (!$u) return false;
        $role = strtolower((string)($u->role ?? ''));
        $is = ($role === 'admin') || str_contains($role, 'admin');
        if (method_exists($u, 'hasRole')) $is = $is || $u->hasRole('admin');
        return $is;
    }

    private function isTeacher($u): bool
    {
        if (!$u) return false;
        $role = strtolower((string)($u->role ?? ''));
        $is = ($role === 'teacher');
        if (method_exists($u, 'hasRole')) $is = $is || $u->hasRole('teacher');
        return $is;
    }

    // =========================
    // Helpers: smart table/column pickers
    // =========================

    private function pickTable(array $candidates): ?string
    {
        foreach ($candidates as $t) {
            if ($t && Schema::hasTable($t)) return $t;
        }
        return null;
    }

    private function pickColumn(string $table, array $candidates): ?string
    {
        foreach ($candidates as $c) {
            if ($c && Schema::hasColumn($table, $c)) return $c;
        }
        return null;
    }
}
