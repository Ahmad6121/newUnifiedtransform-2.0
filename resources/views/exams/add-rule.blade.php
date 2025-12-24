@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="mb-0">Add Exam Rule</h2>
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
                <form method="POST" action="{{ route('exam.rule.store') }}" class="row g-3">
                    @csrf

                    <div class="col-md-4">
                        <label class="form-label">Exam</label>
                        <select class="form-select" name="exam_id" required>
                            <option value="">Select Exam</option>
                            @foreach($exams as $e)
                                <option value="{{ $e->id }}" {{ old('exam_id') == $e->id ? 'selected' : '' }}>
                                    {{ $e->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Class / Grade</label>
                        <select class="form-select" name="class_id" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->{$classNameCol} }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Section</label>
                        <select class="form-select" name="section_id" required>
                            <option value="">Select Section</option>
                            @foreach($sections as $s)
                                <option value="{{ $s->id }}" {{ old('section_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->{$sectionNameCol} }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> Add Rule
                        </button>
                        <a href="{{ route('exam.list.show') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection

