@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="mb-0">Exams</h2>
            <a class="btn btn-primary" href="{{ route('exam.create.show') }}">
                + Create Exam
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger mb-3">
                <ul class="mb-0">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Filters --}}
        <form method="GET" action="{{ route('exam.list.show') }}" class="row g-2 mb-3">
            <div class="col-md-4">
                <label class="form-label">Grade / Class</label>
                <select name="class_id" class="form-select">
                    <option value="">Select Grade</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ (string)$class_id === (string)$c->id ? 'selected' : '' }}>
                            {{ $c->{$classNameCol} }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Semester</label>
                <select name="semester_id" class="form-select">
                    <option value="">Select Semester</option>
                    @foreach($semesters as $s)
                        <option value="{{ $s->id }}" {{ (string)$semester_id === (string)$s->id ? 'selected' : '' }}>
                            {{ $s->{$semesterNameCol} }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary w-100">
                    <i class="bi bi-arrow-clockwise me-1"></i>
                    Load List
                </button>
            </div>
        </form>

        {{-- Table --}}
        <div class="card">
            <div class="card-body">
                @if(!$class_id || !$semester_id)
                    <div class="text-muted">
                        Choose Grade + Semester then click <b>Load List</b>.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Course</th>
                                <th>Created</th>
                                <th>Starts</th>
                                <th>Ends</th>
                                <th>Online</th>
                                <th style="width:220px;">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($exams as $e)
                                <tr>
                                    <td>{{ $e->name }}</td>
                                    <td>
                                        {{ $e->course->name ?? $e->course->course_name ?? $e->course->title ?? '-' }}
                                    </td>
                                    <td>{{ $e->created_at }}</td>
                                    <td>{{ $e->starts ?? '-' }}</td>
                                    <td>{{ $e->ends ?? '-' }}</td>
                                    <td>
                                        @if($e->is_online)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                    <td class="d-flex gap-2">
                                        <a class="btn btn-sm btn-outline-primary"
                                           href="{{ route('exam.rule.create') }}">
                                            + Add Rule
                                        </a>
                                        <a class="btn btn-sm btn-outline-dark"
                                           href="{{ route('exam.rule.show', ['exam_id'=>$e->id]) }}">
                                            View Rule
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-muted">No exams found.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

    </div>
@endsection
