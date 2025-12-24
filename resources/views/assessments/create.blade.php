@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="mb-0">Create Assessment</h3>
                <small class="text-muted">Teacher creates an assessment and assigns weight + marks</small>
            </div>
            <a href="{{ route('assessments.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Please fix the following:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('assessments.store') }}">
                    @csrf

                    {{-- ✅ session_id hidden --}}
                    <input type="hidden" name="session_id" value="{{ $sessionId }}">

                    <div class="row g-3">
                        {{-- Title --}}
                        <div class="col-md-6">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control"
                                   value="{{ old('title') }}" required>
                        </div>

                        {{-- Mode --}}
                        <div class="col-md-6">
                            <label class="form-label">Mode</label>
                            <select name="mode" class="form-select" required>
                                <option value="manual" {{ old('mode')=='manual' ? 'selected' : '' }}>Manual</option>
                                <option value="online" {{ old('mode')=='online' ? 'selected' : '' }}>Online (later builder)</option>
                            </select>
                        </div>

                        {{-- ✅ Class --}}
                        <div class="col-md-4">
                            <label class="form-label">Class</label>

                            {{-- خيار 1: reload عند تغيير الصف --}}
                            <select id="class_id" name="class_id" class="form-select" required
                                    onchange="window.location='{{ url('/assessments/create') }}?class_id=' + this.value;">
                                @foreach($classes as $c)
                                    <option value="{{ $c->id }}"
                                        {{ (int)$selectedClassId === (int)$c->id ? 'selected' : '' }}>
                                        {{ $c->class_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- ✅ Course --}}
                        <div class="col-md-4">
                            <label class="form-label">Course</label>
                            <select id="course_id" name="course_id" class="form-select" required>
                                <option value="">-- Select Course --</option>
                                @foreach($courses as $co)
                                    <option value="{{ $co->id }}"
                                        {{ (string)old('course_id') === (string)$co->id ? 'selected' : '' }}>
                                        {{ $co->course_name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Courses are filtered by selected class (no duplicates).</small>
                        </div>

                        {{-- Section ID optional --}}
                        <div class="col-md-4">
                            <label class="form-label">Section ID (optional)</label>
                            <input type="number" name="section_id" class="form-control"
                                   value="{{ old('section_id') }}">
                        </div>

                        {{-- Total Marks --}}
                        <div class="col-md-4">
                            <label class="form-label">Total Marks</label>
                            <input type="number" step="0.01" min="0" name="total_marks" class="form-control"
                                   value="{{ old('total_marks', 100) }}" required>
                        </div>

                        {{-- Weight % --}}
                        <div class="col-md-4">
                            <label class="form-label">Weight %</label>
                            <input type="number" step="0.01" min="0" max="100" name="weight_percent" class="form-control"
                                   value="{{ old('weight_percent', 0) }}" required>
                            <small class="text-muted">Example: Mid 30%, Final 40%, etc.</small>
                        </div>

                        {{-- Passing Marks --}}
                        <div class="col-md-4">
                            <label class="form-label">Passing Marks (optional)</label>
                            <input type="number" step="0.01" min="0" name="passing_marks" class="form-control"
                                   value="{{ old('passing_marks') }}">
                        </div>

                        {{-- Start Date --}}
                        <div class="col-md-6">
                            <label class="form-label">Start Date (optional)</label>
                            <input type="datetime-local" name="start_date" class="form-control"
                                   value="{{ old('start_date') }}">
                        </div>

                        {{-- End Date --}}
                        <div class="col-md-6">
                            <label class="form-label">Due/End Date (optional)</label>
                            <input type="datetime-local" name="end_date" class="form-control"
                                   value="{{ old('end_date') }}">
                        </div>

                        {{-- Duration --}}
                        <div class="col-md-6">
                            <label class="form-label">Duration (minutes)</label>
                            <input type="number" min="1" name="duration_minutes" class="form-control"
                                   value="{{ old('duration_minutes', 60) }}">
                        </div>

                        {{-- Attempts Allowed --}}
                        <div class="col-md-6">
                            <label class="form-label">Attempts Allowed</label>
                            <input type="number" min="1" name="attempts_allowed" class="form-control"
                                   value="{{ old('attempts_allowed', 1) }}">
                        </div>

                        {{-- Randomize --}}
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="randomize_questions" value="1"
                                    {{ old('randomize_questions') ? 'checked' : '' }}>
                                <label class="form-check-label">Randomize Questions</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-end">
                        <button class="btn btn-primary" type="submit">Create Assessment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ✅ إذا بدك AJAX بدل reload: فعّل الكود تحت (يتطلب route coursesByClass) --}}
    {{--
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var classSelect = document.getElementById('class_id');
        var courseSelect = document.getElementById('course_id');

        classSelect.addEventListener('change', function () {
            var classId = this.value;
            courseSelect.innerHTML = '<option value="">Loading...</option>';

            fetch("{{ route('assessments.coursesByClass') }}?class_id=" + classId)
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    var html = '<option value="">-- Select Course --</option>';
                    for (var i = 0; i < data.length; i++) {
                        html += '<option value="' + data[i].id + '">' + data[i].course_name + '</option>';
                    }
                    courseSelect.innerHTML = html;
                })
                .catch(function () {
                    courseSelect.innerHTML = '<option value="">-- Select Course --</option>';
                });
        });
    });
    </script>
    --}}
@endsection
