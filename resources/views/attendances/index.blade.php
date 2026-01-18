@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')

            <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
                <div class="row pt-2">
                    <div class="col ps-4">

                        <h1 class="display-6 mb-3">
                            <i class="bi bi-calendar2-week"></i> Attendance
                        </h1>

                        <div class="row">
                            <div class="col">
                                <div class="row">

                                    @foreach (($classes_and_sections['school_classes'] ?? collect()) as $school_class)
                                        @php
                                            // ✅ شعب هذا الصف فقط (بدون تكرار)
                                            $classSections = ($sectionsByClass[(int)$school_class->id] ?? collect());
                                        @endphp

                                        <div class="col-12">
                                            <div class="card my-3">
                                                <div class="card-header bg-transparent">
                                                    <i class="bi bi-diagram-2"></i> {{ $school_class->class_name }}
                                                </div>

                                                <div class="card-body text-dark">

                                                    @if (($academic_setting->attendance_type ?? 'section') == 'course')

                                                        @foreach (($courses ?? collect()) as $course)
                                                            @if ((int)($course->class_id ?? 0) === (int)$school_class->id)
                                                                <h6>Course: {{ $course->course_name }}</h6>

                                                                <div class="list-group mb-2">
                                                                    <a href="{{ url('attendances/view?class_id='.$school_class->id.'&class_name='.$school_class->class_name.'&course_id='.$course->id.'&course_name='.$course->course_name) }}"
                                                                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                                        View Attendance
                                                                    </a>

                                                                    <a href="{{ url('attendances/take?class_id='.$school_class->id.'&class_name='.$school_class->class_name.'&course_id='.$course->id.'&course_name='.$course->course_name) }}"
                                                                       class="list-group-item list-group-item-action">
                                                                        Take Attendance
                                                                    </a>
                                                                </div>
                                                            @endif
                                                        @endforeach

                                                    @else

                                                        <div class="tab-content">
                                                            <div class="accordion" id="accordionClass{{ $school_class->id }}">

                                                                @forelse ($classSections as $school_section)
                                                                    <div class="accordion-item">
                                                                        <h2 class="accordion-header" id="headingClass{{ $school_class->id }}Section{{ $school_section->id }}">
                                                                            <button class="accordion-button collapsed" type="button"
                                                                                    data-bs-toggle="collapse"
                                                                                    data-bs-target="#collapseClass{{ $school_class->id }}Section{{ $school_section->id }}"
                                                                                    aria-expanded="false"
                                                                                    aria-controls="collapseClass{{ $school_class->id }}Section{{ $school_section->id }}">
                                                                                {{ $school_section->section_name }}
                                                                            </button>
                                                                        </h2>

                                                                        <div id="collapseClass{{ $school_class->id }}Section{{ $school_section->id }}"
                                                                             class="accordion-collapse collapse"
                                                                             aria-labelledby="headingClass{{ $school_class->id }}Section{{ $school_section->id }}"
                                                                             data-bs-parent="#accordionClass{{ $school_class->id }}">

                                                                            <div class="accordion-body">
                                                                                <div class="list-group mb-2">

                                                                                    <a href="{{ url('attendances/view?class_id='.$school_class->id.'&section_id='.$school_section->id.'&class_name='.$school_class->class_name.'&section_name='.$school_section->section_name) }}"
                                                                                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                                                        View Attendance
                                                                                    </a>

                                                                                    <a href="{{ url('attendances/take?class_id='.$school_class->id.'&class_name='.$school_class->class_name.'&section_id='.$school_section->id.'&section_name='.$school_section->section_name) }}"
                                                                                       class="list-group-item list-group-item-action">
                                                                                        Take Attendance
                                                                                    </a>

                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                @empty
                                                                    <div class="alert alert-warning mb-0">
                                                                        No sections assigned for this class.
                                                                    </div>
                                                                @endforelse

                                                            </div>
                                                        </div>

                                                    @endif

                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                @include('layouts.footer')
            </div>
        </div>
    </div>
@endsection
