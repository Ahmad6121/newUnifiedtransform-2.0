@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="mb-0">Create Exam</h2>
            <a class="btn btn-outline-secondary" href="{{ route('exam.list.show') }}">Back</a>
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

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('exam.create') }}" class="row g-3">
                    @csrf

                    <div class="col-md-6">
                        <label class="form-label">Exam Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Course</label>
                        <select name="course_id" class="form-select" required>
                            <option value="">Select Course</option>
                            @foreach($courses as $c)
                                <option value="{{ $c->id }}" {{ old('course_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->{$courseNameCol} }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Semester (optional)</label>
                        <select name="semester_id" class="form-select">
                            <option value="">Select Semester</option>
                            @foreach($semesters as $s)
                                <option value="{{ $s->id }}" {{ old('semester_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->{$semesterNameCol} }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Starts</label>
                        <input type="datetime-local" name="starts" class="form-control" value="{{ old('starts') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Ends</label>
                        <input type="datetime-local" name="ends" class="form-control" value="{{ old('ends') }}">
                    </div>

                    <hr class="my-2">

                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" value="1" id="is_online" name="is_online"
                                {{ old('is_online') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_online">
                                Online Exam
                            </label>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Duration (minutes)</label>
                        <input type="number" name="duration_minutes" class="form-control" min="1" max="600"
                               value="{{ old('duration_minutes', 60) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Max Attempts</label>
                        <input type="number" name="max_attempts" class="form-control" min="1" max="20"
                               value="{{ old('max_attempts', 1) }}">
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button class="btn btn-primary">
                            <i class="bi bi-check2-circle me-1"></i> Save
                        </button>
                        <a href="{{ route('exam.list.show') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection
