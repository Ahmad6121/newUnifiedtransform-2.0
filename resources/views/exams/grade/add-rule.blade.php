@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="mb-0">Add Grade Rule</h2>
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
                <form method="POST" action="{{ route('exam.grade.system.rule.store') }}" class="row g-3">
                    @csrf

                    <div class="col-md-6">
                        <label class="form-label">Grading System</label>
                        <select name="grading_system_id" class="form-select" required>
                            <option value="">Select System</option>
                            @foreach($systems as $s)
                                <option value="{{ $s->id }}" {{ old('grading_system_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Min %</label>
                        <input type="number" name="min_percent" class="form-control" min="0" max="100"
                               value="{{ old('min_percent') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Max %</label>
                        <input type="number" name="max_percent" class="form-control" min="0" max="100"
                               value="{{ old('max_percent') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Grade</label>
                        <input type="text" name="grade" class="form-control" value="{{ old('grade') }}" required>
                    </div>

                    <div class="col-md-9">
                        <label class="form-label">Remark (optional)</label>
                        <input type="text" name="remark" class="form-control" value="{{ old('remark') }}">
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> Add Rule
                        </button>
                        <a class="btn btn-outline-secondary" href="{{ route('exam.grade.system.index') }}">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
