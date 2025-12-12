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
                {{-- Students / My Children --}}
                <li class="nav-item">
                    @if(auth()->user()->hasRole('parent'))
                        {{-- للأهل: يفتح صفحة My Children --}}
                        <a class="nav-link {{ request()->routeIs('parent.children') ? 'active' : '' }}"
                           href="{{ route('parent.children') }}">
                            <i class="bi bi-people"></i>
                            <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">My Children</span>
                        </a>
                    @else
                        {{-- لأي دور آخر: يفتح صفحة الطلاب العادية --}}
                <li class="nav-item">
                    <a type="button" href="#student-submenu" data-bs-toggle="collapse" class="d-flex nav-link {{ request()->is('students*')? 'active' : '' }}"><i class="bi bi-person-lines-fill"></i> <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Students</span>
                        <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                    </a>
                    <ul class="nav collapse {{ request()->is('students*')? 'show' : 'hide' }} bg-white" id="student-submenu">
                        <li class="nav-item w-100" {{ request()->routeIs('student.list.show')? 'style="font-weight:bold;"' : '' }}><a class="nav-link" href="{{route('student.list.show')}}"><i class="bi bi-person-video2 me-2"></i> View Students</a></li>
                        @if (!session()->has('browse_session_id') && Auth::user()->role == "admin")
                            <li class="nav-item w-100" {{ request()->routeIs('student.create.show')? 'style="font-weight:bold;"' : '' }}><a class="nav-link" href="{{route('student.create.show')}}"><i class="bi bi-person-plus me-2"></i> Add Student</a></li>
                        @endif
                    </ul>
                </li>
                    @endif
                </li>

                {{-- Teachers submenu --}}
                <li class="nav-item">
                    <a type="button"
                       href="#teacher-submenu"
                       data-bs-toggle="collapse"
                       class="d-flex nav-link {{ request()->is('teachers*') ? 'active' : '' }}">
                        <i class="bi bi-person-lines-fill"></i>
                        <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Teachers</span>
                        <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                    </a>

                    <ul class="nav collapse {{ request()->is('teachers*') ? 'show' : 'hide' }} bg-white"
                        id="teacher-submenu">
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
                       href="{{ route('course.teacher.list.show', ['teacher_id' => Auth::user()->id]) }}">
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
                           href="{{ route('section.routine.show', [
                                'class_id'  => $class_info->class_id,
                                'section_id'=> $class_info->section_id
                           ]) }}">
                            <i class="bi bi-calendar4-range"></i>
                            <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Routine</span>
                        </a>
                    @endif
                </li>
            @endif

            {{-- Exams / Grades (للأدمن + المدرّس + الأهل) --}}
            @if (Auth::user()->role != 'student')
                <li class="nav-item border-bottom">
                    <a type="button"
                       href="#exam-grade-submenu"
                       data-bs-toggle="collapse"
                       class="d-flex nav-link {{ request()->is('exams*') ? 'active' : '' }}">
                        <i class="bi bi-file-text"></i>
                        <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Exams / Grades</span>
                        <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                    </a>

                    <ul class="nav collapse {{ request()->is('exams*') ? 'show' : 'hide' }} bg-white"
                        id="exam-grade-submenu">
                        <li class="nav-item w-100" style="{{ request()->routeIs('exam.list.show') ? 'font-weight:bold;' : '' }}">
                            <a class="nav-link" href="{{ route('exam.list.show') }}">
                                <i class="bi bi-file-text me-2"></i> View Exams
                            </a>
                        </li>

                        @if (Auth::user()->role == 'admin' || Auth::user()->role == 'teacher')
                            <li class="nav-item w-100" style="{{ request()->routeIs('exam.create.show') ? 'font-weight:bold;' : '' }}">
                                <a class="nav-link" href="{{ route('exam.create.show') }}">
                                    <i class="bi bi-file-plus me-2"></i> Create Exams
                                </a>
                            </li>
                        @endif

                        @if (Auth::user()->role == 'admin')
                            <li class="nav-item w-100" style="{{ request()->routeIs('exam.grade.system.create') ? 'font-weight:bold;' : '' }}">
                                <a class="nav-link" href="{{ route('exam.grade.system.create') }}">
                                    <i class="bi bi-file-plus me-2"></i> Add Grade Systems
                                </a>
                            </li>
                        @endif

                        <li class="nav-item w-100" style="{{ request()->routeIs('exam.grade.system.index') ? 'font-weight:bold;' : '' }}">
                            <a class="nav-link" href="{{ route('exam.grade.system.index') }}">
                                <i class="bi bi-file-ruled me-2"></i> View Grade Systems
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            {{-- Admin-only block --}}
            @if (Auth::user()->role == 'admin')
                <li class="nav-item">
                    <a href="{{ route('admin.users.index') }}" class="nav-link">
                        <i class="bi bi-people"></i> <span> Rools </span>
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
