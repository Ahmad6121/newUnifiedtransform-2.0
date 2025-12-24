<div class="col-xs-1 col-sm-1 col-md-1 col-lg-2 col-xl-2 col-xxl-2 border-rt-e6 px-0">
    <div class="d-flex flex-column align-items-center align-items-sm-start min-vh-100">
        <ul class="nav flex-column pt-2 w-100">

            {{-- Dashboard --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->is('home') ? 'active' : '' }}" href="{{ url('home') }}">
                    <i class="ms-auto bi bi-grid"></i>
                    <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">{{ __('Dashboard') }}</span>
                </a>
            </li>

            {{-- Classes (permission based) --}}
            @can('view classes')
                <li class="nav-item">
                    @php
                        if (session()->has('browse_session_id')) {
                            $classCount = \App\Models\SchoolClass::where('session_id', session('browse_session_id'))->count();
                        } else {
                            $latest_session = \App\Models\SchoolSession::latest()->first();
                            if ($latest_session) {
                                $classCount = \App\Models\SchoolClass::where('session_id', $latest_session->id)->count();
                            } else {
                                $classCount = 0;
                            }
                        }
                    @endphp

                    <a class="nav-link d-flex {{ request()->is('classes') ? 'active' : '' }}" href="{{ url('classes') }}">
                        <i class="bi bi-diagram-3"></i>
                        <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Classes</span>
                        <span class="ms-auto d-inline d-sm-none d-md-none d-xl-inline">{{ $classCount }}</span>
                    </a>
                </li>
            @endcan

            {{-- Students / My Children + Teachers (للأدمن و المدرّس و الأهل) --}}
            @if(Auth::user()->role != 'student')

                {{-- Parent: My Children --}}
                @if(auth()->user()->hasRole('parent'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('parent.children') ? 'active' : '' }}"
                           href="{{ route('parent.children') }}">
                            <i class="bi bi-people"></i>
                            <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">My Children</span>
                        </a>
                    </li>
                @else
                    {{-- Students submenu (Admin/Teacher/Other) --}}
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
                                <a class="nav-link" href="{{ route('student.list.show') }}">
                                    <i class="bi bi-person-video2 me-2"></i> View Students
                                </a>
                            </li>

                            @if (!session()->has('browse_session_id') && Auth::user()->role == "admin")
                                <li class="nav-item w-100" style="{{ request()->routeIs('student.create.show') ? 'font-weight:bold;' : '' }}">
                                    <a class="nav-link" href="{{ route('student.create.show') }}">
                                        <i class="bi bi-person-plus me-2"></i> Add Student
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

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
                            <a class="nav-link" href="{{ route('teacher.list.show') }}">
                                <i class="bi bi-person-video2 me-2"></i> View Teachers
                            </a>
                        </li>

                        @if (!session()->has('browse_session_id') && Auth::user()->role == 'admin')
                            <li class="nav-item w-100" style="{{ request()->routeIs('teacher.create.show') ? 'font-weight:bold;' : '' }}">
                                <a class="nav-link" href="{{ route('teacher.create.show') }}">
                                    <i class="bi bi-person-plus me-2"></i> Add Teacher
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            {{-- Teacher menu --}}
            @if (Auth::user()->role == 'teacher')
                <li class="nav-item">
                    <a class="nav-link {{ (request()->is('courses/teacher*') || request()->is('courses/assignments*')) ? 'active' : '' }}"
                       href="{{ route('course.teacher.list.show') }}">
                        <i class="bi bi-journal-medical"></i>
                        <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">My Courses</span>
                    </a>
                </li>
            @endif

            {{-- Student menu --}}
            @if (Auth::user()->role == 'student')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('student.attendance.show') ? 'active' : '' }}"
                       href="{{ route('student.attendance.show', ['id' => Auth::user()->id]) }}">
                        <i class="bi bi-calendar2-week"></i>
                        <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Attendance</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('course.student.list.show') ? 'active' : '' }}"
                       href="{{ route('course.student.list.show', ['student_id' => Auth::user()->id]) }}">
                        <i class="bi bi-journal-medical"></i>
                        <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Courses</span>
                    </a>
                </li>

                <li class="nav-item border-bottom">
                    @php
                        if (session()->has('browse_session_id')) {
                            $class_info = \App\Models\Promotion::where('session_id', session('browse_session_id'))
                                ->where('student_id', Auth::user()->id)
                                ->first();
                        } else {
                            $latest_session = \App\Models\SchoolSession::latest()->first();
                            if ($latest_session) {
                                $class_info = \App\Models\Promotion::where('session_id', $latest_session->id)
                                    ->where('student_id', Auth::user()->id)
                                    ->first();
                            } else {
                                $class_info = null;
                            }
                        }
                    @endphp

                    @if ($class_info)
                        <a class="nav-link"
                           href="{{ route('section.routine.show', ['class_id' => $class_info->class_id, 'section_id'=> $class_info->section_id]) }}">
                            <i class="bi bi-calendar4-range"></i>
                            <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Routine</span>
                        </a>
                    @endif
                </li>
            @endif

            {{-- =========================
                 Exams & Grades (NEW CLEAN)
                 ========================= --}}
            <li class="nav-item border-bottom">
                @php
                    $isAssessmentsActive =
                        request()->is('assessments*')
                        || request()->is('my-assessments*')
                        || request()->is('gradebook*')
                        || request()->is('reports*')
                        || request()->is('report-card*')
                        || request()->is('student/grades*');
                @endphp

                <a type="button"
                   href="#exams-grades-submenu"
                   data-bs-toggle="collapse"
                   class="d-flex nav-link {{ $isAssessmentsActive ? 'active' : '' }}">
                    <i class="bi bi-clipboard-check"></i>
                    <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Exams & Grades</span>
                    <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                </a>

                <ul class="nav collapse {{ $isAssessmentsActive ? 'show' : 'hide' }} bg-white" id="exams-grades-submenu">

                    {{-- Admin/Teacher --}}
                    @if(Auth::user()->role == 'admin' || Auth::user()->role == 'teacher')
                        <li class="nav-item w-100" style="{{ request()->routeIs('assessments.dashboard') ? 'font-weight:bold;' : '' }}">
                            <a class="nav-link" href="{{ route('assessments.dashboard') }}">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>

                        <li class="nav-item w-100" style="{{ request()->routeIs('assessments.index') ? 'font-weight:bold;' : '' }}">
                            <a class="nav-link" href="{{ route('assessments.index') }}">
                                <i class="bi bi-list-check me-2"></i> My Assessments
                            </a>
                        </li>

                        <li class="nav-item w-100" style="{{ request()->routeIs('assessments.create') ? 'font-weight:bold;' : '' }}">
                            <a class="nav-link" href="{{ route('assessments.create') }}">
                                <i class="bi bi-plus-circle me-2"></i> Create Assessment
                            </a>
                        </li>

                        <li class="nav-item w-100" style="{{ request()->routeIs('gradebook.index') ? 'font-weight:bold;' : '' }}">
                            <a class="nav-link" href="{{ route('gradebook.index') }}">
                                <i class="bi bi-journal-text me-2"></i> Gradebook
                            </a>
                        </li>

                        <li class="nav-item w-100" style="{{ request()->routeIs('reports.exports') ? 'font-weight:bold;' : '' }}">
                            <a class="nav-link" href="{{ route('reports.exports') }}">
                                <i class="bi bi-download me-2"></i> Reports & Exports
                            </a>
                        </li>
                    @endif

                    {{-- Student --}}
                    @if(Auth::user()->role == 'student')
                        <li class="nav-item w-100" style="{{ request()->routeIs('student.assessments.available') ? 'font-weight:bold;' : '' }}">
                            <a class="nav-link" href="{{ route('student.assessments.available') }}">
                                <i class="bi bi-ui-checks-grid me-2"></i> My Exams
                            </a>
                        </li>

                        <li class="nav-item w-100" style="{{ request()->routeIs('student.grades.index') ? 'font-weight:bold;' : '' }}">
                            <a class="nav-link" href="{{ route('student.grades.index') }}">
                                <i class="bi bi-bar-chart-line me-2"></i> My Grades
                            </a>
                        </li>

                        <li class="nav-item w-100" style="{{ request()->routeIs('reportcard.my') ? 'font-weight:bold;' : '' }}">
                            <a class="nav-link" href="{{ route('reportcard.my') }}">
                                <i class="bi bi-file-earmark-text me-2"></i> Report Card
                            </a>
                        </li>
                    @endif

                    {{-- Parent --}}
                    @if(auth()->user()->hasRole('parent'))
                        <li class="nav-item w-100" style="{{ request()->routeIs('parent.children') ? 'font-weight:bold;' : '' }}">
                            <a class="nav-link" href="{{ route('parent.children') }}">
                                <i class="bi bi-people me-2"></i> Children Results
                            </a>
                        </li>
                    @endif

                </ul>
            </li>

            {{-- Admin-only block --}}
            @if (Auth::user()->role == 'admin')

                <li class="nav-item">
                    <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i>
                        <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Roles</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->is('notice*') ? 'active' : '' }}" href="{{ route('notice.create') }}">
                        <i class="bi bi-megaphone"></i>
                        <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Notice</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->is('calendar-event*') ? 'active' : '' }}" href="{{ route('events.show') }}">
                        <i class="bi bi-calendar-event"></i>
                        <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Event</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->is('syllabus*') ? 'active' : '' }}" href="{{ route('class.syllabus.create') }}">
                        <i class="bi bi-journal-text"></i>
                        <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Syllabus</span>
                    </a>
                </li>

                <li class="nav-item border-bottom">
                    <a class="nav-link {{ request()->is('routine*') ? 'active' : '' }}" href="{{ route('section.routine.create') }}">
                        <i class="bi bi-calendar4-range"></i>
                        <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Routine</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->is('academics*') ? 'active' : '' }}" href="{{ url('academics/settings') }}">
                        <i class="bi bi-tools"></i>
                        <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Academic</span>
                    </a>
                </li>

                @if (!session()->has('browse_session_id'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('promotions*') ? 'active' : '' }}" href="{{ url('promotions/index') }}">
                            <i class="bi bi-sort-numeric-up-alt"></i>
                            <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Promotion</span>
                        </a>
                    </li>
                @endif

                <li class="nav-item">
                    <a class="nav-link {{ request()->is('staff*') ? 'active' : '' }}" href="{{ route('staff.index') }}">
                        <i class="bi bi-person-lines-fill"></i>
                        <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Staff</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->is('library/books*') ? 'active' : '' }}" href="{{ route('library.books.index') }}">
                        <i class="bi bi-journals"></i>
                        <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Library</span>
                    </a>
                </li>
            @endif

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
