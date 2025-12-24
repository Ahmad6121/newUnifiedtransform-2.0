<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StaffController;

use App\Http\Controllers\EventController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\RoutineController;
use App\Http\Controllers\SyllabusController;

use App\Http\Controllers\CourseController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SchoolClassController;

use App\Http\Controllers\AttendanceController;

use App\Http\Controllers\SchoolSessionController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\AcademicSettingController;

use App\Http\Controllers\AssignedTeacherController;

use App\Http\Controllers\PromotionController;
use App\Http\Controllers\AssignmentController;

use App\Http\Controllers\Auth\UpdatePasswordController;

use App\Http\Controllers\Finance\InvoiceController;
use App\Http\Controllers\Finance\PaymentController;

use App\Http\Controllers\Library\BookController;
use App\Http\Controllers\Library\BookIssueController;

use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\ParentDashboardController;

/**
 * =========================
 * Exams & Grades (NEW CLEAN)
 * =========================
 */
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AssessmentQuestionController;
use App\Http\Controllers\AssessmentTakeController;
use App\Http\Controllers\AssessmentGradingController;

use App\Http\Controllers\GradebookController;
use App\Http\Controllers\AssessmentMarksController;

use App\Http\Controllers\StudentGradesController;
use App\Http\Controllers\ReportCardController;

use App\Http\Controllers\AssessmentDashboardController;

use App\Http\Controllers\ReportsExportController;
use App\Http\Controllers\ReportsPageController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Parent Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('parent')->name('parent.')->group(function () {
        Route::get('/dashboard', [ParentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/progress', [ParentDashboardController::class, 'progress'])->name('progress');
        Route::get('/children', [UserController::class, 'getMyChildren'])->name('children');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin - User Roles
    |--------------------------------------------------------------------------
    */
    Route::get('admin/users', [UserRoleController::class, 'index'])->name('admin.users.index');
    Route::get('admin/users/{user}/edit', [UserRoleController::class, 'edit'])->name('admin.users.edit');
    Route::put('admin/users/{user}', [UserRoleController::class, 'update'])->name('admin.users.update');
    Route::get('admin/users/search', [UserRoleController::class, 'search'])->name('admin.users.search');

    /*
    |--------------------------------------------------------------------------
    | School Settings
    |--------------------------------------------------------------------------
    */
    Route::prefix('school')->name('school.')->group(function () {

        Route::post('session/create', [SchoolSessionController::class, 'store'])->name('session.store');
        Route::post('session/browse', [SchoolSessionController::class, 'browse'])->name('session.browse');

        Route::post('semester/create', [SemesterController::class, 'store'])->name('semester.create');

        Route::post('final-marks-submission-status/update', [AcademicSettingController::class, 'updateFinalMarksSubmissionStatus'])
            ->name('final.marks.submission.status.update');

        Route::post('attendance/type/update', [AcademicSettingController::class, 'updateAttendanceType'])
            ->name('attendance.type.update');

        // Class
        Route::post('class/create', [SchoolClassController::class, 'store'])->name('class.create');
        Route::post('class/update', [SchoolClassController::class, 'update'])->name('class.update');

        // Sections
        Route::post('section/create', [SectionController::class, 'store'])->name('section.create');
        Route::post('section/update', [SectionController::class, 'update'])->name('section.update');

        // Courses
        Route::post('course/create', [CourseController::class, 'store'])->name('course.create');
        Route::post('course/update', [CourseController::class, 'update'])->name('course.update');

        // Teacher
        Route::post('teacher/create', [UserController::class, 'storeTeacher'])->name('teacher.create');
        Route::post('teacher/update', [UserController::class, 'updateTeacher'])->name('teacher.update');
        Route::post('teacher/assign', [AssignedTeacherController::class, 'store'])->name('teacher.assign');

        // Student
        Route::post('student/create', [UserController::class, 'storeStudent'])->name('student.create');
        Route::post('student/update', [UserController::class, 'updateStudent'])->name('student.update');
    });

    Route::get('/home', [HomeController::class, 'index'])->name('home');

    /*
    |--------------------------------------------------------------------------
    | Attendance
    |--------------------------------------------------------------------------
    */
    Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendances/view', [AttendanceController::class, 'show'])->name('attendance.list.show');
    Route::get('/attendances/take', [AttendanceController::class, 'create'])->name('attendance.create.show');
    Route::post('/attendances', [AttendanceController::class, 'store'])->name('attendances.store');

    /*
    |--------------------------------------------------------------------------
    | Classes & Sections
    |--------------------------------------------------------------------------
    */
    Route::get('/classes', [SchoolClassController::class, 'index']);
    Route::get('/class/edit/{id}', [SchoolClassController::class, 'edit'])->name('class.edit');

    Route::get('/sections', [SectionController::class, 'getByClassId'])->name('get.sections.courses.by.classId');
    Route::get('/section/edit/{id}', [SectionController::class, 'edit'])->name('section.edit');

    /*
    |--------------------------------------------------------------------------
    | Teachers
    |--------------------------------------------------------------------------
    */
    Route::get('/teachers/add', function () {
        return view('teachers.add');
    })->name('teacher.create.show');

    Route::get('/teachers/edit/{id}', [UserController::class, 'editTeacher'])->name('teacher.edit.show');
    Route::get('/teachers/view/list', [UserController::class, 'getTeacherList'])->name('teacher.list.show');
    Route::get('/teachers/view/profile/{id}', [UserController::class, 'showTeacherProfile'])->name('teacher.profile.show');

    /*
    |--------------------------------------------------------------------------
    | Students
    |--------------------------------------------------------------------------
    */
    Route::get('/students/add', [UserController::class, 'createStudent'])->name('student.create.show');
    Route::get('/students/edit/{id}', [UserController::class, 'editStudent'])->name('student.edit.show');
    Route::get('/students/view/list', [UserController::class, 'getStudentList'])->name('student.list.show');
    Route::get('/students/view/profile/{id}', [UserController::class, 'showStudentProfile'])->name('student.profile.show');
    Route::get('/students/view/attendance/{id}', [AttendanceController::class, 'showStudentAttendance'])->name('student.attendance.show');

    /*
    |--------------------------------------------------------------------------
    | Courses
    |--------------------------------------------------------------------------
    */
    Route::get('courses/teacher/index', [AssignedTeacherController::class, 'getTeacherCourses'])->name('course.teacher.list.show');
    Route::get('courses/student/index/{student_id}', [CourseController::class, 'getStudentCourses'])->name('course.student.list.show');
    Route::get('course/edit/{id}', [CourseController::class, 'edit'])->name('course.edit');

    /*
    |--------------------------------------------------------------------------
    | Assignment
    |--------------------------------------------------------------------------
    */
    Route::get('courses/assignments/index', [AssignmentController::class, 'getCourseAssignments'])->name('assignment.list.show');
    Route::get('courses/assignments/create', [AssignmentController::class, 'create'])->name('assignment.create');
    Route::post('courses/assignments/create', [AssignmentController::class, 'store'])->name('assignment.store');

    /*
    |--------------------------------------------------------------------------
    | Promotions
    |--------------------------------------------------------------------------
    */
    Route::get('/promotions/index', [PromotionController::class, 'index'])->name('promotions.index');
    Route::get('/promotions/promote', [PromotionController::class, 'create'])->name('promotions.create');
    Route::post('/promotions/promote', [PromotionController::class, 'store'])->name('promotions.store');

    /*
    |--------------------------------------------------------------------------
    | Academic settings
    |--------------------------------------------------------------------------
    */
    Route::get('/academics/settings', [AcademicSettingController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Calendar events
    |--------------------------------------------------------------------------
    */
    Route::get('calendar-event', [EventController::class, 'index'])->name('events.show');
    Route::post('calendar-crud-ajax', [EventController::class, 'calendarEvents'])->name('events.crud');

    /*
    |--------------------------------------------------------------------------
    | Routines
    |--------------------------------------------------------------------------
    */
    Route::get('/routine/create', [RoutineController::class, 'create'])->name('section.routine.create');
    Route::get('/routine/view', [RoutineController::class, 'show'])->name('section.routine.show');
    Route::post('/routine/store', [RoutineController::class, 'store'])->name('section.routine.store');

    /*
    |--------------------------------------------------------------------------
    | Syllabus
    |--------------------------------------------------------------------------
    */
    Route::get('/syllabus/create', [SyllabusController::class, 'create'])->name('class.syllabus.create');
    Route::post('/syllabus/create', [SyllabusController::class, 'store'])->name('syllabus.store');
    Route::get('/syllabus/index', [SyllabusController::class, 'index'])->name('course.syllabus.index');

    /*
    |--------------------------------------------------------------------------
    | Notices
    |--------------------------------------------------------------------------
    */
    Route::get('/notice/create', [NoticeController::class, 'create'])->name('notice.create');
    Route::post('/notice/create', [NoticeController::class, 'store'])->name('notice.store');

    /*
    |--------------------------------------------------------------------------
    | Update password
    |--------------------------------------------------------------------------
    */
    Route::get('password/edit', [UpdatePasswordController::class, 'edit'])->name('password.edit');
    Route::post('password/edit', [UpdatePasswordController::class, 'update'])->name('password.update');

    /*
    |--------------------------------------------------------------------------
    | Finance
    |--------------------------------------------------------------------------
    */
    Route::resource('finance/invoices', InvoiceController::class)->names([
        'index'   => 'finance.invoices.index',
        'create'  => 'finance.invoices.create',
        'store'   => 'finance.invoices.store',
        'show'    => 'finance.invoices.show',
        'edit'    => 'finance.invoices.edit',
        'update'  => 'finance.invoices.update',
        'destroy' => 'finance.invoices.destroy',
    ]);

    Route::post('finance/invoices/{invoice}/payments', [PaymentController::class,'store'])
        ->name('finance.invoices.payments.store');

    /*
    |--------------------------------------------------------------------------
    | Staff
    |--------------------------------------------------------------------------
    */
    Route::resource('staff', StaffController::class);

    /*
    |--------------------------------------------------------------------------
    | Library
    |--------------------------------------------------------------------------
    */
    Route::resource('library/books', BookController::class)->names([
        'index'   => 'library.books.index',
        'create'  => 'library.books.create',
        'store'   => 'library.books.store',
        'show'    => 'library.books.show',
        'edit'    => 'library.books.edit',
        'update'  => 'library.books.update',
        'destroy' => 'library.books.destroy',
    ]);

    Route::get('library/issues', [BookIssueController::class,'index'])->name('library.issues.index');
    Route::get('library/issues/create', [BookIssueController::class,'create'])->name('library.issues.create');
    Route::post('library/issues', [BookIssueController::class,'store'])->name('library.issues.store');
    Route::post('library/issues/{issue}/return', [BookIssueController::class,'return'])->name('library.issues.return');
    Route::get('library/issues/{issue}/edit', [BookIssueController::class,'edit'])->name('library.issues.edit');
    Route::put('library/issues/{issue}', [BookIssueController::class,'update'])->name('library.issues.update');

    /*
    |--------------------------------------------------------------------------
    | =========================
    | Exams & Grades (NEW CLEAN)
    | =========================
    |--------------------------------------------------------------------------
    */

// Dashboard
    Route::get('/assessments/dashboard', [AssessmentDashboardController::class, 'index'])
        ->name('assessments.dashboard');

// Assessments CRUD (teacher/admin usage)
    Route::get('/assessments', [AssessmentController::class, 'index'])->name('assessments.index');
    Route::get('/assessments/create', [AssessmentController::class, 'create'])->name('assessments.create');
    Route::post('/assessments', [AssessmentController::class, 'store'])->name('assessments.store');

    Route::get('/assessments/{assessment}/edit', [AssessmentController::class, 'edit'])->name('assessments.edit');
    Route::put('/assessments/{assessment}', [AssessmentController::class, 'update'])->name('assessments.update');

    Route::delete('/assessments/{assessment}', [AssessmentController::class, 'destroy'])
        ->name('assessments.destroy');

// Publish / Unpublish / Close
    Route::post('/assessments/{assessment}/publish', [AssessmentController::class, 'publish'])
        ->name('assessments.publish');

    Route::post('/assessments/{assessment}/unpublish', [AssessmentController::class, 'unpublish'])
        ->name('assessments.unpublish');

    Route::post('/assessments/{assessment}/close', [AssessmentController::class, 'close'])
        ->name('assessments.close');

// Publish/Hide Results
    Route::post('/assessments/{assessment}/results/publish', [AssessmentController::class, 'publishResults'])
        ->name('assessments.results.publish');

    Route::post('/assessments/{assessment}/results/hide', [AssessmentController::class, 'unpublishResults'])
        ->name('assessments.results.hide');


// Question Builder (online)
    Route::get('/assessments/{assessment}/questions', [AssessmentQuestionController::class, 'index'])
        ->name('assessments.questions.index');

    Route::post('/assessments/{assessment}/questions', [AssessmentQuestionController::class, 'store'])
        ->name('assessments.questions.store');

    Route::post('/assessments/questions/{id}/delete', [AssessmentQuestionController::class, 'destroy'])
        ->name('assessments.questions.delete');


// Student: My Assessments
    Route::get('/my-assessments', [AssessmentTakeController::class, 'available'])
        ->name('student.assessments.available');

    Route::get('/my-assessments/{assessment}/start', [AssessmentTakeController::class, 'start'])
        ->name('student.assessments.start');

    Route::get('/my-assessments/attempts/{attempt}/take', [AssessmentTakeController::class, 'take'])
        ->name('student.assessments.take');

    Route::post('/my-assessments/attempts/{attempt}/submit', [AssessmentTakeController::class, 'submit'])
        ->name('student.assessments.submit');

    Route::get('/my-assessments/attempts/{attempt}/result', [AssessmentTakeController::class, 'result'])
        ->name('student.assessments.result');


// Teacher/Admin: Attempts Grading
    Route::get('/assessments/{assessment}/attempts', [AssessmentGradingController::class, 'attempts'])
        ->name('teacher.assessments.attempts');

    Route::get('/assessments/attempts/{attempt}/review', [AssessmentGradingController::class, 'review'])
        ->name('teacher.assessments.review');

    Route::post('/assessments/answers/{answer}/grade', [AssessmentGradingController::class, 'gradeAnswer'])
        ->name('teacher.assessments.answer.grade');

    Route::post('/assessments/attempts/{attempt}/finalize', [AssessmentGradingController::class, 'finalize'])
        ->name('teacher.assessments.finalize');


// Manual marks entry (for Written/Project/etc)
    Route::get('/assessments/{assessment}/marks', [AssessmentMarksController::class, 'edit'])
        ->name('assessments.marks.edit');

    Route::post('/assessments/{assessment}/marks', [AssessmentMarksController::class, 'update'])
        ->name('assessments.marks.update');


// Gradebook
    Route::get('/gradebook', [GradebookController::class, 'index'])->name('gradebook.index');
    Route::get('/gradebook/course/{course}', [GradebookController::class, 'course'])->name('gradebook.course');


// Student Grades + Report Card
    Route::get('/student/grades', [StudentGradesController::class, 'index'])->name('student.grades.index');

    Route::get('/report-card/my', [ReportCardController::class, 'my'])->name('reportcard.my');
    Route::get('/report-card/child/{student}', [ReportCardController::class, 'child'])->name('reportcard.child');
    Route::get('/parent/children/{student}/report-card', [ReportCardController::class, 'child'])
        ->name('reportcard.child');


// Reports & Exports page (Sidebar route)
    Route::get('/reports/exports', [ReportsPageController::class, 'exports'])->name('reports.exports');

// Exports
    Route::get('/reports/assessments/{assessment}/csv', [ReportsExportController::class, 'assessmentCsv'])
        ->name('reports.assessment.csv');

    Route::get('/reports/assessments/{assessment}/pdf', [ReportsExportController::class, 'assessmentPdf'])
        ->name('reports.assessment.pdf');

    Route::get('/reports/class-gradebook/csv', [ReportsExportController::class, 'classGradebookCsv'])
        ->name('reports.class.gradebook.csv');

    Route::get('/reports/class-gradebook/pdf', [ReportsExportController::class, 'classGradebookPdf'])
        ->name('reports.class.gradebook.pdf');


});
