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

// Library
use App\Http\Controllers\Library\BookController;
use App\Http\Controllers\Library\BookIssueController;
use App\Http\Controllers\Library\LibraryReportController;

// Job Titles
use App\Http\Controllers\JobTitleController;

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
use App\Http\Controllers\MessagingController;

use App\Http\Controllers\AccountantController;

use App\Http\Controllers\Finance\FinanceDashboardController;
use App\Http\Controllers\Finance\ExpenseController;
use App\Http\Controllers\Finance\PayrollController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::middleware(['auth'])->group(function () {

    /*
    |----------------------------------------------------------------------
    | Parent Routes
    |----------------------------------------------------------------------
    */
    Route::prefix('parent')->name('parent.')->group(function () {
        Route::get('/dashboard', [ParentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/progress', [ParentDashboardController::class, 'progress'])->name('progress');
        Route::get('/children', [UserController::class, 'getMyChildren'])->name('children');

        Route::get('/children/{student}/report-card', [ReportCardController::class, 'child'])
            ->name('reportcard.child');
    });



    /*
    |----------------------------------------------------------------------
    | Admin - User Roles
    |----------------------------------------------------------------------
    */
    Route::get('admin/users', [UserRoleController::class, 'index'])->name('admin.users.index');
    Route::get('admin/users/{user}/edit', [UserRoleController::class, 'edit'])->name('admin.users.edit');
    Route::put('admin/users/{user}', [UserRoleController::class, 'update'])->name('admin.users.update');
    Route::get('admin/users/search', [UserRoleController::class, 'search'])->name('admin.users.search');

    /*
    |----------------------------------------------------------------------
    | School Settings
    |----------------------------------------------------------------------
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
    |----------------------------------------------------------------------
    | Attendance
    |----------------------------------------------------------------------
    */
    Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendances/view', [AttendanceController::class, 'show'])->name('attendance.list.show');
    Route::get('/attendances/take', [AttendanceController::class, 'create'])->name('attendance.create.show');
    Route::post('/attendances', [AttendanceController::class, 'store'])->name('attendances.store');

    /*
    |----------------------------------------------------------------------
    | Classes & Sections
    |----------------------------------------------------------------------
    */
    Route::get('/classes', [SchoolClassController::class, 'index']);
    Route::get('/class/edit/{id}', [SchoolClassController::class, 'edit'])->name('class.edit');

    Route::get('/sections', [SectionController::class, 'getByClassId'])->name('get.sections.courses.by.classId');
    Route::get('/section/edit/{id}', [SectionController::class, 'edit'])->name('section.edit');

    /*
    |----------------------------------------------------------------------
    | Teachers
    |----------------------------------------------------------------------
    */
    Route::get('/teachers/add', function () {
        return view('teachers.add');
    })->name('teacher.create.show');

    Route::get('/teachers/edit/{id}', [UserController::class, 'editTeacher'])->name('teacher.edit.show');
    Route::get('/teachers/view/list', [UserController::class, 'getTeacherList'])->name('teacher.list.show');
    Route::get('/teachers/view/profile/{id}', [UserController::class, 'showTeacherProfile'])->name('teacher.profile.show');

    /*
    |----------------------------------------------------------------------
    | Students
    |----------------------------------------------------------------------
    */
    Route::get('/students/add', [UserController::class, 'createStudent'])->name('student.create.show');
    Route::get('/students/edit/{id}', [UserController::class, 'editStudent'])->name('student.edit.show');
    Route::get('/students/view/list', [UserController::class, 'getStudentList'])->name('student.list.show');
    Route::get('/students/view/profile/{id}', [UserController::class, 'showStudentProfile'])->name('student.profile.show');
    Route::get('/students/view/attendance/{id}', [AttendanceController::class, 'showStudentAttendance'])
        ->name('student.attendance.show');

    /*
    |----------------------------------------------------------------------
    | Courses
    |----------------------------------------------------------------------
    */
    Route::get('courses/teacher/index', [AssignedTeacherController::class, 'getTeacherCourses'])->name('course.teacher.list.show');
    Route::get('courses/student/index/{student_id}', [CourseController::class, 'getStudentCourses'])->name('course.student.list.show');
    Route::get('course/edit/{id}', [CourseController::class, 'edit'])->name('course.edit');

    /*
    |----------------------------------------------------------------------
    | Assignment
    |----------------------------------------------------------------------
    */
    Route::get('courses/assignments/index', [AssignmentController::class, 'getCourseAssignments'])->name('assignment.list.show');
    Route::get('courses/assignments/create', [AssignmentController::class, 'create'])->name('assignment.create');
    Route::post('courses/assignments/create', [AssignmentController::class, 'store'])->name('assignment.store');

    /*
    |----------------------------------------------------------------------
    | Promotions
    |----------------------------------------------------------------------
    */
    Route::get('/promotions/index', [PromotionController::class, 'index'])->name('promotions.index');
    Route::get('/promotions/promote', [PromotionController::class, 'create'])->name('promotions.create');
    Route::post('/promotions/promote', [PromotionController::class, 'store'])->name('promotions.store');

    /*
    |----------------------------------------------------------------------
    | Academic settings
    |----------------------------------------------------------------------
    */
    Route::get('/academics/settings', [AcademicSettingController::class, 'index']);

    /*
    |----------------------------------------------------------------------
    | Calendar events
    |----------------------------------------------------------------------
    */
    Route::get('calendar-event', [EventController::class, 'index'])->name('events.show');
    Route::post('calendar-crud-ajax', [EventController::class, 'calendarEvents'])->name('events.crud');

    /*
    |----------------------------------------------------------------------
    | Routines
    |----------------------------------------------------------------------
    */
    Route::get('/routine/create', [RoutineController::class, 'create'])->name('section.routine.create');
    Route::get('/routine/view', [RoutineController::class, 'show'])->name('section.routine.show');
    Route::post('/routine/store', [RoutineController::class, 'store'])->name('section.routine.store');

    /*
    |----------------------------------------------------------------------
    | Syllabus
    |----------------------------------------------------------------------
    */
    Route::get('/syllabus/create', [SyllabusController::class, 'create'])->name('class.syllabus.create');
    Route::post('/syllabus/create', [SyllabusController::class, 'store'])->name('syllabus.store');
    Route::get('/syllabus/index', [SyllabusController::class, 'index'])->name('course.syllabus.index');

    /*
    |----------------------------------------------------------------------
    | Notices
    |----------------------------------------------------------------------
    */
    Route::get('/notice/create', [NoticeController::class, 'create'])->name('notice.create');
    Route::post('/notice/create', [NoticeController::class, 'store'])->name('notice.store');

    /*
    |----------------------------------------------------------------------
    | Update password
    |----------------------------------------------------------------------
    */
    Route::get('password/edit', [UpdatePasswordController::class, 'edit'])->name('password.edit');
    Route::post('password/edit', [UpdatePasswordController::class, 'update'])->name('password.update');

    /*
 |--------------------------------------------------------------------------
 | Finance
 |--------------------------------------------------------------------------
 */



    Route::middleware(['auth'])
        ->prefix('finance')
        ->name('finance.')
        ->group(function () {

            // ✅ Dashboard
            Route::get('dashboard', [FinanceDashboardController::class, 'index'])
                ->name('dashboard');

            // ✅ Expenses CRUD
            Route::resource('expenses', ExpenseController::class)->names([
                'index' => 'expenses.index',
                'create' => 'expenses.create',
                'store' => 'expenses.store',
                'edit' => 'expenses.edit',
                'update' => 'expenses.update',
                'destroy' => 'expenses.destroy',
            ])->except(['show']);

            // ✅ Payroll
            Route::get('payroll', [PayrollController::class, 'index'])->name('payroll.index');
            Route::get('payroll/create', [PayrollController::class, 'create'])->name('payroll.create');
            Route::post('payroll', [PayrollController::class, 'store'])->name('payroll.store');
            Route::get('payroll/{payroll}/edit', [PayrollController::class, 'edit'])->name('payroll.edit');
            Route::put('payroll/{payroll}', [PayrollController::class, 'update'])->name('payroll.update');
            Route::delete('payroll/{payroll}', [PayrollController::class, 'destroy'])->name('payroll.destroy');
            Route::post('payroll/set-salary', [PayrollController::class, 'setSalary'])->name('payroll.setSalary');
            Route::post('payroll/pay', [PayrollController::class, 'pay'])->name('payroll.pay');


            // ✅ AJAX (Class -> Sections) + (Class+Section -> Students)
            // ✅ مهم: داخل finance group عشان الاسم يصير finance.invoices.ajaxSections
            Route::get('invoices/ajax/sections', [InvoiceController::class, 'ajaxSections'])
                ->name('invoices.ajaxSections');

            Route::get('invoices/ajax/students', [InvoiceController::class, 'ajaxStudents'])
                ->name('invoices.ajaxStudents');

            // ✅ (اختياري) إذا عندك مسار ثاني مستخدمه
            Route::get('invoices/students/by-class-section', [InvoiceController::class, 'studentsByClassSection'])
                ->name('invoices.studentsByClassSection');

            // ✅ Resource routes (الأفضل خليها)
            Route::resource('invoices', InvoiceController::class)->names([
                'index'   => 'invoices.index',
                'create'  => 'invoices.create',
                'store'   => 'invoices.store',
                'show'    => 'invoices.show',
                'edit'    => 'invoices.edit',
                'update'  => 'invoices.update',
                'destroy' => 'invoices.destroy',
            ]);

            // ✅ Quick Payment
            Route::post('invoices/{invoice}/quick-payment', [InvoiceController::class, 'quickPayment'])
                ->name('invoices.quickPayment');

            // ✅ Print PDF
            Route::get('invoices/{invoice}/print', [InvoiceController::class, 'printInvoice'])
                ->name('invoices.print');

            // ✅ Payments store
            Route::post('invoices/{invoice}/payments', [PaymentController::class, 'store'])
                ->name('invoices.payments.store');
        });

    /*
 |----------------------------------------------------------------------
 | Staff  (/staff)  -> names: staff.employees.*
 |----------------------------------------------------------------------
 */
    Route::middleware(['auth'])->group(function () {

        // ✅ Staff routes: names match your views (staff.employees.*)
        Route::prefix('staff')->name('staff.employees.')->group(function () {
            Route::get('/', [StaffController::class, 'index'])->name('index');
            Route::get('/create', [StaffController::class, 'create'])->name('create');
            Route::post('/', [StaffController::class, 'store'])->name('store');

            Route::get('/{employee}/edit', [StaffController::class, 'edit'])->name('edit');
            Route::put('/{employee}', [StaffController::class, 'update'])->name('update');
            Route::delete('/{employee}', [StaffController::class, 'destroy'])->name('destroy');
        });

    });

    Route::resource('job-titles', \App\Http\Controllers\JobTitleController::class)->except(['show']);


    /*
    |----------------------------------------------------------------------
    | Accountants  (/accountants)  -> names: accountants.*
    |----------------------------------------------------------------------
    */

    Route::middleware(['auth'])->group(function () {

        // ✅ index فقط (Admin + Accountant)
        Route::get('/accountants', [AccountantController::class, 'index'])
            ->name('accountants.index');

        // ✅ باقي العمليات (Admin فقط)
        Route::middleware(function ($request, $next) {
            $u = auth()->user();
            if (!$u || $u->role !== 'admin') abort(403);
            return $next($request);
        })->group(function () {

            Route::get('/accountants/create', [AccountantController::class, 'create'])
                ->name('accountants.create');

            Route::post('/accountants', [AccountantController::class, 'store'])
                ->name('accountants.store');

            Route::get('/accountants/{user}/edit', [AccountantController::class, 'edit'])
                ->name('accountants.edit');

            Route::put('/accountants/{user}', [AccountantController::class, 'update'])
                ->name('accountants.update');

            Route::delete('/accountants/{user}', [AccountantController::class, 'destroy'])
                ->name('accountants.destroy');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Job Titles ✅ (/job-titles)  (اختياري إذا بدك تخليه مستقل)
    |--------------------------------------------------------------------------
    */


    /*
    |----------------------------------------------------------------------
    | Library ✅ (/library/books) بدون تكرار /library/library
    |----------------------------------------------------------------------
    */
    Route::prefix('library')->name('library.')->group(function () {

        // Books
        Route::resource('books', BookController::class)->except(['show']);

        // Issues
        Route::get('issues/create', [BookIssueController::class, 'create'])->name('issues.create');
        Route::post('issues', [BookIssueController::class, 'store'])->name('issues.store');
        Route::post('issues/{issue}/return', [BookIssueController::class, 'returnBook'])->name('issues.return');

        // Reports
        Route::get('reports', [LibraryReportController::class, 'index'])->name('reports.index');
    });

    /*
    |----------------------------------------------------------------------
    | =========================
    | Exams & Grades (NEW CLEAN)
    | =========================
    |----------------------------------------------------------------------
    */

    // Dashboard
    Route::get('/assessments/dashboard', [AssessmentDashboardController::class, 'index'])->name('assessments.dashboard');

    // Assessments CRUD
    Route::get('/assessments', [AssessmentController::class, 'index'])->name('assessments.index');
    Route::get('/assessments/create', [AssessmentController::class, 'create'])->name('assessments.create');
    Route::post('/assessments', [AssessmentController::class, 'store'])->name('assessments.store');

    Route::get('/assessments/{assessment}/edit', [AssessmentController::class, 'edit'])->name('assessments.edit');
    Route::put('/assessments/{assessment}', [AssessmentController::class, 'update'])->name('assessments.update');
    Route::delete('/assessments/{assessment}', [AssessmentController::class, 'destroy'])->name('assessments.destroy');

    // Publish / Unpublish / Close
    Route::post('/assessments/{assessment}/publish', [AssessmentController::class, 'publish'])->name('assessments.publish');
    Route::post('/assessments/{assessment}/unpublish', [AssessmentController::class, 'unpublish'])->name('assessments.unpublish');
    Route::post('/assessments/{assessment}/close', [AssessmentController::class, 'close'])->name('assessments.close');

    // Publish/Hide Results
    Route::post('/assessments/{assessment}/results/publish', [AssessmentController::class, 'publishResults'])->name('assessments.results.publish');
    Route::post('/assessments/{assessment}/results/hide', [AssessmentController::class, 'unpublishResults'])->name('assessments.results.hide');

    // Question Builder
    Route::get('/assessments/{assessment}/questions', [AssessmentQuestionController::class, 'index'])->name('assessments.questions.index');
    Route::post('/assessments/{assessment}/questions', [AssessmentQuestionController::class, 'store'])->name('assessments.questions.store');
    Route::post('/assessments/questions/{id}/delete', [AssessmentQuestionController::class, 'destroy'])->name('assessments.questions.delete');

    // Student: My Assessments
    Route::get('/my-assessments', [AssessmentTakeController::class, 'available'])->name('student.assessments.available');
    Route::get('/my-assessments/{assessment}/start', [AssessmentTakeController::class, 'start'])->name('student.assessments.start');
    Route::get('/my-assessments/attempts/{attempt}/take', [AssessmentTakeController::class, 'take'])->name('student.assessments.take');
    Route::post('/my-assessments/attempts/{attempt}/submit', [AssessmentTakeController::class, 'submit'])->name('student.assessments.submit');
    Route::get('/my-assessments/attempts/{attempt}/result', [AssessmentTakeController::class, 'result'])->name('student.assessments.result');

    // Teacher/Admin: Attempts Grading
    Route::get('/assessments/{assessment}/attempts', [AssessmentGradingController::class, 'attempts'])->name('teacher.assessments.attempts');
    Route::get('/assessments/attempts/{attempt}/review', [AssessmentGradingController::class, 'review'])->name('teacher.assessments.review');
    Route::post('/assessments/answers/{answer}/grade', [AssessmentGradingController::class, 'gradeAnswer'])->name('teacher.assessments.answer.grade');
    Route::post('/assessments/attempts/{attempt}/finalize', [AssessmentGradingController::class, 'finalize'])->name('teacher.assessments.finalize');

    // Manual marks entry
    Route::get('/assessments/{assessment}/marks', [AssessmentMarksController::class, 'edit'])->name('assessments.marks.edit');
    Route::post('/assessments/{assessment}/marks', [AssessmentMarksController::class, 'update'])->name('assessments.marks.update');

    // Gradebook
    Route::get('/gradebook', [GradebookController::class, 'index'])->name('gradebook.index');
    Route::get('/gradebook/course/{course}', [GradebookController::class, 'course'])->name('gradebook.course');

    // Student Grades + Report Card
    Route::get('/student/grades', [StudentGradesController::class, 'index'])->name('student.grades.index');
    Route::get('/report-card/my', [ReportCardController::class, 'my'])->name('reportcard.my');
    Route::get('/report-card/child/{student}', [ReportCardController::class, 'child'])->name('reportcard.child');
//    Route::get('/parent/children/{student}/report-card', [ReportCardController::class, 'child'])->name('reportcard.child');

    // Reports & Exports page
    Route::get('/reports/exports', [ReportsPageController::class, 'exports'])->name('reports.exports');

    // Exports
    Route::get('/reports/assessments/{assessment}/csv', [ReportsExportController::class, 'assessmentCsv'])->name('reports.assessment.csv');
    Route::get('/reports/assessments/{assessment}/pdf', [ReportsExportController::class, 'assessmentPdf'])->name('reports.assessment.pdf');
    Route::get('/reports/class-gradebook/csv', [ReportsExportController::class, 'classGradebookCsv'])->name('reports.class.gradebook.csv');
    Route::get('/reports/class-gradebook/pdf', [ReportsExportController::class, 'classGradebookPdf'])->name('reports.class.gradebook.pdf');

    /*
    |----------------------------------------------------------------------
    | Messages / Chat
    |----------------------------------------------------------------------
    */
    Route::get('/messages', [MessagingController::class, 'index'])->name('messages.index');

    Route::get('/messages/conversations', [MessagingController::class, 'conversations']);
    Route::post('/messages/start', [MessagingController::class, 'start']);
    Route::get('/messages/{id}/list', [MessagingController::class, 'messages']);
    Route::post('/messages/{id}/send', [MessagingController::class, 'send']);
    Route::post('/messages/{id}/admin-join', [MessagingController::class, 'adminJoin']);
    Route::get('/messages/users/search', [MessagingController::class, 'userSearch']);
});
