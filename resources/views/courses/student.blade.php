@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')

            <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
                <div class="row pt-2">
                    <div class="col ps-4">

                        <h1 class="display-6 mb-3">
                            <i class="bi bi-journal-medical"></i> My Courses
                        </h1>

                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">My courses</li>
                            </ol>
                        </nav>

                        <div class="mb-4 mt-4">
                            <div class="p-3 mt-3 bg-white border shadow-sm">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th scope="col">Course Name</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @isset($courses)
                                        @forelse ($courses as $course)
                                            <tr>
                                                <td>{{ $course->course_name }}</td>
                                                <td>
                                                    <div class="btn-group" role="group">

                                                        {{-- ✅ View Marks (FIX): route course.mark.show غير موجود عندك --}}
                                                        {{-- إذا رجّعته بالمستقبل، هذا الشرط رح يستخدمه، وإذا مش موجود رح يودّي لصفحة الطالب Grades --}}
                                                        @if(\Illuminate\Support\Facades\Route::has('course.mark.show'))
                                                            <a href="{{ route('course.mark.show', [
                                                                'course_id' => $course->id,
                                                                'course_name' => $course->course_name,
                                                                'semester_id' => $course->semester_id,
                                                                'class_id'  => $class_info->class_id ?? null,
                                                                'session_id' => $course->session_id,
                                                                'section_id' => $class_info->section_id ?? null,
                                                                'student_id' => Auth::user()->id
                                                            ]) }}"
                                                               role="button"
                                                               class="btn btn-sm btn-outline-primary">
                                                                <i class="bi bi-award"></i> View Marks
                                                            </a>
                                                        @else
                                                            <a href="{{ route('student.grades.index') }}"
                                                               role="button"
                                                               class="btn btn-sm btn-outline-primary">
                                                                <i class="bi bi-bar-chart-line"></i> My Marks
                                                            </a>
                                                        @endif

                                                        {{-- Syllabus --}}
                                                        <a href="{{ route('course.syllabus.index', ['course_id'  => $course->id]) }}"
                                                           role="button"
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-journal-text"></i> View Syllabus
                                                        </a>

                                                        {{-- Assignments --}}
                                                        <a href="{{ route('assignment.list.show', ['course_id' => $course->id]) }}"
                                                           role="button"
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-file-post"></i> View Assignments
                                                        </a>

                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-muted">No courses found.</td>
                                            </tr>
                                        @endforelse
                                    @endisset
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>

                @include('layouts.footer')
            </div>
        </div>
    </div>
@endsection
