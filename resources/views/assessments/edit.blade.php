@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0">Edit Assessment</h4>
                <div class="text-muted">Update assessment details</div>
            </div>
            <a class="btn btn-outline-dark" href="{{ route('assessments.index') }}">Back</a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <div class="fw-bold mb-2">Please fix the following:</div>
                <ul class="mb-0">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('assessments.update', $assessment->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Title</label>
                            <input name="title" class="form-control" value="{{ old('title', $assessment->title) }}" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Kind</label>
                            <select name="kind" class="form-select" required>
                                @php $kind = old('kind', $assessment->kind); @endphp
                                <option value="exam" {{ $kind=='exam'?'selected':'' }}>Exam</option>
                                <option value="quiz" {{ $kind=='quiz'?'selected':'' }}>Quiz</option>
                                <option value="assignment" {{ $kind=='assignment'?'selected':'' }}>Assignment</option>
                                <option value="project" {{ $kind=='project'?'selected':'' }}>Project</option>
                                <option value="research" {{ $kind=='research'?'selected':'' }}>Research</option>
                                <option value="oral" {{ $kind=='oral'?'selected':'' }}>Oral</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Mode</label>
                            <select name="mode" class="form-select" required>
                                @php $mode = old('mode', $assessment->mode); @endphp
                                <option value="manual" {{ $mode=='manual'?'selected':'' }}>Manual</option>
                                <option value="online" {{ $mode=='online'?'selected':'' }}>Online</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Class</label>
                            <select name="class_id" class="form-select">
                                <option value="">--</option>
                                @foreach($classes as $c)
                                    <option value="{{ $c->id }}" {{ (string)old('class_id', $assessment->class_id) === (string)$c->id ? 'selected' : '' }}>
                                        {{ $c->class_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Course</label>
                            <select name="course_id" class="form-select">
                                <option value="">--</option>
                                @foreach($courses as $co)
                                    <option value="{{ $co->id }}" {{ (string)old('course_id', $assessment->course_id) === (string)$co->id ? 'selected' : '' }}>
                                        {{ $co->course_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Section (optional)</label>
                            <select name="section_id" class="form-select">
                                <option value="">--</option>
                                @foreach($sections as $s)
                                    <option value="{{ $s->id }}" {{ (string)old('section_id', $assessment->section_id) === (string)$s->id ? 'selected' : '' }}>
                                        {{ $s->name ?? ('Section #'.$s->id) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Total Marks</label>
                            <input type="number" step="0.01" name="total_marks" class="form-control"
                                   value="{{ old('total_marks', $assessment->total_marks) }}" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Passing Marks</label>
                            <input type="number" step="0.01" name="passing_marks" class="form-control"
                                   value="{{ old('passing_marks', $assessment->passing_marks ?? 50) }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Weight %</label>
                            <input type="number" step="0.01" name="weight_percent" class="form-control"
                                   value="{{ old('weight_percent', $assessment->weight_percent) }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            @php $status = old('status', $assessment->status); @endphp
                            <select name="status" class="form-select" required>
                                <option value="draft" {{ $status=='draft'?'selected':'' }}>Draft</option>
                                <option value="published" {{ $status=='published'?'selected':'' }}>Published</option>
                                <option value="closed" {{ $status=='closed'?'selected':'' }}>Closed</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Start Date (optional)</label>
                            <input type="datetime-local" name="start_date" class="form-control"
                                   value="{{ old('start_date', $assessment->start_date ? \Carbon\Carbon::parse($assessment->start_date)->format('Y-m-d\TH:i') : '' ) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">End Date (optional)</label>
                            <input type="datetime-local" name="end_date" class="form-control"
                                   value="{{ old('end_date', $assessment->end_date ? \Carbon\Carbon::parse($assessment->end_date)->format('Y-m-d\TH:i') : '' ) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Duration Minutes (optional)</label>
                            <input type="number" name="duration_minutes" class="form-control"
                                   value="{{ old('duration_minutes', $assessment->duration_minutes) }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Attempts Allowed</label>
                            <input type="number" name="attempts_allowed" class="form-control"
                                   value="{{ old('attempts_allowed', $assessment->attempts_allowed ?? 1) }}">
                        </div>

                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_randomized" name="is_randomized"
                                    {{ old('is_randomized', $assessment->is_randomized) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_randomized">Randomize Questions</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description (optional)</label>
                            <textarea name="description" rows="3" class="form-control">{{ old('description', $assessment->description) }}</textarea>
                        </div>

                        <div class="col-12 d-flex justify-content-end">
                            <button class="btn btn-dark">Save Changes</button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
