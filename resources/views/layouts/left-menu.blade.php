<div class="col-xs-1 col-sm-1 col-md-1 col-lg-2 col-xl-2 col-xxl-2 border-rt-e6 px-0">
    <div class="d-flex flex-column align-items-center align-items-sm-start min-vh-100">
        <ul class="nav flex-column pt-2 w-100">

            @php
                $u = Auth::user();

                // Safe route helper: if route doesn't exist, use fallback URL
                $r = function (string $name, $fallback = '#', array $params = []) {
                    return \Illuminate\Support\Facades\Route::has($name) ? route($name, $params) : $fallback;
                };

                $role = $u ? ($u->role ?? '') : '';

                $isAdmin  = $u && (method_exists($u, 'isAdmin') ? $u->isAdmin() : ($role === 'admin'));
                $isTeacher = $u && (method_exists($u, 'isTeacher') ? $u->isTeacher() : ($role === 'teacher'));
                $isStudent = $u && (method_exists($u, 'isStudent') ? $u->isStudent() : ($role === 'student'));
                $isAccountant = $u && (method_exists($u, 'isAccountant') ? $u->isAccountant() : ($role === 'accountant'));

                $isParent  = $u && (
                    (method_exists($u, 'isParentRole') ? $u->isParentRole() : false)
                    || ((method_exists($u, 'hasRole') && $u->hasRole('parent')) ? true : false)
                    || ($role === 'parent')
                );

                // Safe urls
                $accountantsUrl = \Illuminate\Support\Facades\Route::has('accountants.index')
                    ? route('accountants.index')
                    : url('/accountants');

                $messagesUrl = $r('messages.index', url('/messages'));
                $paymentsUrl = $r('finance.invoices.index', url('/finance/invoices'));

                $studentsListUrl = $r('student.list.show', url('/students/view/list'));
                $teachersListUrl = $r('teacher.list.show', url('/teachers/view/list'));

                // Admin urls (SAFE)
                $rolesUrl   = $r('admin.users.index', url('/admin/users'));
                $noticeUrl  = $r('notice.create', url('/notice/create'));
                $eventUrl   = $r('events.show', url('/calendar-event/show'));
                $syllabusUrl= $r('class.syllabus.create', url('/syllabus/create'));
                $routineCreateUrl = $r('section.routine.create', url('/routine/create'));
                $academicUrl = url('academics/settings');
                $promotionUrl = url('promotions/index');

                // Staff (SAFE)
                $staffUrl = \Illuminate\Support\Facades\Route::has('staff.index')
                    ? route('staff.index')
                    : (\Illuminate\Support\Facades\Route::has('staff.employees.index')
                        ? route('staff.employees.index')
                        : url('/staff'));

                // Library (SAFE)
                $libraryUrl = \Illuminate\Support\Facades\Route::has('library.books.index')
                    ? route('library.books.index')
                    : url('/library/books');
            @endphp

            {{-- Dashboard --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->is('home') ? 'active' : '' }}" href="{{ url('home') }}">
                    <i class="ms-auto bi bi-grid"></i>
                    <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">{{ __('Dashboard') }}</span>
                </a>
            </li>

            {{-- Classes --}}
            @can('view classes')
                <li class="nav-item">
                    @php
                        if (session()->has('browse_session_id')) {
                            $classCount = \App\Models\SchoolClass::where('session_id', session('browse_session_id'))->count();
                        } else {
                            $latest_session = \App\Models\SchoolSession::latest()->first();
                            $classCount = $latest_session
                                ? \App\Models\SchoolClass::where('session_id', $latest_session->id)->count()
                                : 0;
                        }
                    @endphp

                    <a class="nav-link d-flex {{ request()->is('classes') ? 'active' : '' }}" href="{{ url('classes') }}">
                        <i class="bi bi-diagram-3"></i>
                        <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Classes</span>
                        <span class="ms-auto d-inline d-sm-none d-md-none d-xl-inline">{{ $classCount }}</span>
                    </a>
                </li>
            @endcan


            {{-- =========================
               Students / Teachers (لغير الطالب)
               ✅ (صار يطلع للمحاسب كمان View فقط)
               ========================= --}}
            @if($u && !$isStudent)

                {{-- Parent: My Children --}}
                @if($isParent)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('parent.children') ? 'active' : '' }}"
                           href="{{ $r('parent.children', url('/parent/children')) }}">
                            <i class="bi bi-people"></i>
                            <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">My Children</span>
                        </a>
                    </li>


                            <li class="nav-item w-100" style="{{ request()->routeIs('teacher.list.show') ? 'font-weight:bold;' : '' }}">
                                <a class="nav-link" href="{{ $teachersListUrl }}">
                                    <i class="bi bi-person-video2 me-2"></i> View Teachers
                                </a>
                            </li>

                @else

                    {{-- Students submenu --}}
                    <li class="nav-item">
                        <a type="button"
                           href="#student-submenu"
                           data-bs-toggle="collapse"
                           class="d-flex nav-link {{ request()->is('students*') ? 'active' : '' }}">
                            <i class="bi bi-person-lines-fill"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Students</span>
                            <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                        </a>

                        <ul class="nav collapse {{ request()->is('students*') ? 'show' : 'hide' }} bg-white" id="student-submenu">
                            <li class="nav-item w-100" style="{{ request()->routeIs('student.list.show') ? 'font-weight:bold;' : '' }}">
                                <a class="nav-link" href="{{ $studentsListUrl }}">
                                    <i class="bi bi-person-video2 me-2"></i> View Students
                                </a>
                            </li>

                            {{-- Add Student ONLY admin --}}
                            @if (!session()->has('browse_session_id') && $isAdmin)
                                <li class="nav-item w-100" style="{{ request()->routeIs('student.create.show') ? 'font-weight:bold;' : '' }}">
                                    <a class="nav-link" href="{{ $r('student.create.show', url('/students/add')) }}">
                                        <i class="bi bi-person-plus me-2"></i> Add Student
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>

                    {{-- Teachers submenu --}}
                    <li class="nav-item">
                        <a type="button"
                           href="#teacher-submenu"
                           data-bs-toggle="collapse"
                           class="d-flex nav-link {{ request()->is('teachers*') ? 'active' : '' }}">
                            <i class="bi bi-person-badge"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Teachers</span>
                            <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                        </a>

                        <ul class="nav collapse {{ request()->is('teachers*') ? 'show' : 'hide' }} bg-white" id="teacher-submenu">
                            <li class="nav-item w-100" style="{{ request()->routeIs('teacher.list.show') ? 'font-weight:bold;' : '' }}">
                                <a class="nav-link" href="{{ $teachersListUrl }}">
                                    <i class="bi bi-person-video2 me-2"></i> View Teachers
                                </a>
                            </li>

                            {{-- Add Teacher ONLY admin --}}
                            @if (!session()->has('browse_session_id') && $isAdmin)
                                <li class="nav-item w-100" style="{{ request()->routeIs('teacher.create.show') ? 'font-weight:bold;' : '' }}">
                                    <a class="nav-link" href="{{ $r('teacher.create.show', url('/teachers/add')) }}">
                                        <i class="bi bi-person-plus me-2"></i> Add Teacher
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>

                @endif
            @endif


            {{-- Teacher menu --}}
            @if ($u && $isTeacher)
                <li class="nav-item">
                    <a class="nav-link {{ (request()->is('courses/teacher*') || request()->is('courses/assignments*')) ? 'active' : '' }}"
                       href="{{ $r('course.teacher.list.show', url('/courses/teacher/index')) }}">
                        <i class="bi bi-journal-medical"></i>
                        <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">My Courses</span>
                    </a>
                </li>
            @endif


            {{-- Student menu --}}
            @if ($u && $isStudent)

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('student.attendance.show') ? 'active' : '' }}"
                       href="{{ $r('student.attendance.show', url('/students/view/attendance/'.$u->id), ['id' => $u->id]) }}">
                        <i class="bi bi-calendar2-week"></i>
                        <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Attendance</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('course.student.list.show') ? 'active' : '' }}"
                       href="{{ $r('course.student.list.show', url('/courses/student/index/'.$u->id), ['student_id' => $u->id]) }}">
                        <i class="bi bi-journal-medical"></i>
                        <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Courses</span>
                    </a>
                </li>

                {{-- ✅ Teachers للطالب (نفس صفحة المعلمين لكن صارت filtered من الكونترولر) --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('teachers*') ? 'active' : '' }}"
                       href="{{ $teachersListUrl }}">
                        <i class="bi bi-person-badge"></i>
                        <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Teachers</span>
                    </a>
                </li>

                <li class="nav-item border-bottom">
                    @php
                        if (session()->has('browse_session_id')) {
                            $class_info = \App\Models\Promotion::where('session_id', session('browse_session_id'))
                                ->where('student_id', $u->id)
                                ->first();
                        } else {
                            $latest_session = \App\Models\SchoolSession::latest()->first();
                            $class_info = $latest_session
                                ? \App\Models\Promotion::where('session_id', $latest_session->id)->where('student_id', $u->id)->first()
                                : null;
                        }
                    @endphp

                    @if ($class_info)
                        <a class="nav-link"
                           href="{{ $r('section.routine.show', url('/routine/show'), ['class_id' => $class_info->class_id, 'section_id'=> $class_info->section_id]) }}">
                            <i class="bi bi-calendar4-range"></i>
                            <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Routine</span>
                        </a>
                    @endif
                </li>

            @endif


            {{-- ✅ Accountants (Admin + Accountant فقط) --}}
            @if($u && ($isAdmin || $isAccountant))
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('accountants*') ? 'active' : '' }}"
                       href="{{ $accountantsUrl }}">
                        <i class="bi bi-calculator"></i>
                        <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Accountants</span>
                    </a>
                </li>
            @endif


            {{-- ✅ Exams & Grades (ممنوع للمحاسب) --}}
            @if(!$isAccountant)
                <li class="nav-item border-bottom">
                    @php
                        $isAssessmentsActive =
                            request()->is('assessments*')
                            || request()->is('my-assessments*')
                            || request()->is('gradebook*')
                            || request()->is('reports*')
                            || request()->is('report-card*')
                            || request()->is('student/grades*')
                            || request()->is('parent/children*');
                    @endphp
                    @if($u && ($isAdmin || $isTeacher))
                    <a type="button"
                       href="#exams-grades-submenu"
                       data-bs-toggle="collapse"
                       class="d-flex nav-link {{ $isAssessmentsActive ? 'active' : '' }}">
                        <i class="bi bi-clipboard-check"></i>
                        <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Exams & Grades</span>
                        <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                    </a>


                    <ul class="nav collapse {{ $isAssessmentsActive ? 'show' : 'hide' }} bg-white" id="exams-grades-submenu">
                        @endif
                        {{-- Admin/Teacher --}}
                        @if($u && ($isAdmin || $isTeacher))
                            <li class="nav-item w-100">
                                <a class="nav-link" href="{{ $r('assessments.dashboard', url('/assessments/dashboard')) }}">
                                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                                </a>
                            </li>

                            <li class="nav-item w-100">
                                <a class="nav-link" href="{{ $r('assessments.index', url('/assessments')) }}">
                                    <i class="bi bi-list-check me-2"></i> My Assessments
                                </a>
                            </li>

                            <li class="nav-item w-100">
                                <a class="nav-link" href="{{ $r('assessments.create', url('/assessments/create')) }}">
                                    <i class="bi bi-plus-circle me-2"></i> Create Assessment
                                </a>
                            </li>

                            <li class="nav-item w-100">
                                <a class="nav-link" href="{{ $r('gradebook.index', url('/gradebook')) }}">
                                    <i class="bi bi-journal-text me-2"></i> Gradebook
                                </a>
                            </li>

                            <li class="nav-item w-100">
                                <a class="nav-link" href="{{ $r('reports.exports', url('/reports/exports')) }}">
                                    <i class="bi bi-download me-2"></i> Reports & Exports
                                </a>
                            </li>
                        @endif

{{--                        --}}{{-- Parent --}}
{{--                        @if($u && $isParent)--}}
{{--                            <li class="nav-item w-100">--}}
{{--                                <a class="nav-link" href="{{ $r('parent.children', url('/parent/children')) }}">--}}
{{--                                    <i class="bi bi-file-earmark-text me-2"></i> Children Report Cards--}}
{{--                                </a>--}}
{{--                            </li>--}}
{{--                        @endif--}}


                        {{-- Student --}}
                        @if($u && $isStudent)
                            <li class="nav-item w-100">
                                <a class="nav-link" href="{{ $r('student.assessments.available', url('/my-assessments')) }}">
                                    <i class="bi bi-ui-checks-grid me-2"></i> My Exams
                                </a>
                            </li>

                            <li class="nav-item w-100">
                                <a class="nav-link" href="{{ $r('student.grades.index', url('/student/grades')) }}">
                                    <i class="bi bi-bar-chart-line me-2"></i> My Grades
                                </a>
                            </li>

                            <li class="nav-item w-100">
                                <a class="nav-link" href="{{ $r('reportcard.my', url('/report-card/my')) }}">
                                    <i class="bi bi-file-earmark-text me-2"></i> Report Card
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif



            {{-- =========================
               Admin-only block (رجّعناه كامل)
               ========================= --}}
            @if ($u && $isAdmin)

                <li class="nav-item">
                    <a href="{{ $rolesUrl }}" class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i>
                        <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Roles</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->is('notice*') ? 'active' : '' }}" href="{{ $noticeUrl }}">
                        <i class="bi bi-megaphone"></i>
                        <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Notice</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->is('calendar-event*') ? 'active' : '' }}" href="{{ $eventUrl }}">
                        <i class="bi bi-calendar-event"></i>
                        <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Event</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->is('syllabus*') ? 'active' : '' }}" href="{{ $syllabusUrl }}">
                        <i class="bi bi-journal-text"></i>
                        <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Syllabus</span>
                    </a>
                </li>

                <li class="nav-item border-bottom">
                    <a class="nav-link {{ request()->is('routine*') ? 'active' : '' }}" href="{{ $routineCreateUrl }}">
                        <i class="bi bi-calendar4-range"></i>
                        <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Routine</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->is('academics*') ? 'active' : '' }}" href="{{ $academicUrl }}">
                        <i class="bi bi-tools"></i>
                        <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Academic</span>
                    </a>
                </li>

                @if (!session()->has('browse_session_id'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('promotions*') ? 'active' : '' }}" href="{{ $promotionUrl }}">
                            <i class="bi bi-sort-numeric-up-alt"></i>
                            <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Promotion</span>
                        </a>
                    </li>
                @endif

                <li class="nav-item">
                    <a class="nav-link {{ request()->is('staff*') ? 'active' : '' }}" href="{{ $staffUrl }}">
                        <i class="bi bi-person-lines-fill"></i>
                        <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Staff</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->is('library*') ? 'active' : '' }}" href="{{ $libraryUrl }}">
                        <i class="bi bi-journals"></i>
                        <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Library</span>
                    </a>
                </li>

            @endif


            {{-- Messages --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->is('messages*') ? 'active' : '' }}" href="{{ $messagesUrl }}">
                    <i class="bi bi-chat-dots"></i>
                    <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Messages</span>
                </a>

            </li>

            {{-- Payments --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->is('finance/invoices*') ? 'active' : '' }}"
                   href="{{ route('finance.invoices.index') }}">
                    <i class="bi bi-currency-exchange"></i>
                    <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Payments</span>
                </a>
            </li>

        </ul>
    </div>
</div>
