@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="mb-0">Create Grade System</h2>
            <a class="btn btn-outline-secondary" href="{{ route('exam.grade.system.index') }}">Back</a>
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
                <form method="POST" action="{{ route('exam.grade.system.store') }}" class="row g-3">
                    @csrf

                    <div class="col-md-6">
                        <label class="form-label">System Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Class (optional)</label>
                        <select name="class_id" class="form-select">
                            <option value="">All</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->{$classNameCol} }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Semester (optional)</label>
                        <select name="semester_id" class="form-select">
                            <option value="">All</option>
                            @foreach($semesters as $s)
                                <option value="{{ $s->id }}" {{ old('semester_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->{$semesterNameCol} }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button class="btn btn-primary">
                            <i class="bi bi-check2-circle me-1"></i> Save
                        </button>
                        <a class="btn btn-outline-secondary" href="{{ route('exam.grade.system.index') }}">Cancel</a>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection
