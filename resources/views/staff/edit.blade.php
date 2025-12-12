@extends('layouts.app')
@section('content')
    <div class="container">
        <h3>Edit Staff #{{ $staff->id }} — {{ $staff->full_name }}</h3>

        @if(session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif
        @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <form method="POST" action="{{ route('staff.update',$staff) }}" class="row g-3">
            @csrf @method('PUT')

            <div class="col-md-4">
                <label class="form-label">Job title *</label>
                <input name="job_title" class="form-control" required value="{{ old('job_title',$staff->job_title) }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Salary type *</label>
                <select name="salary_type" class="form-select" required>
                    @foreach(['fixed','hourly'] as $t)
                        <option value="{{ $t }}" @selected($staff->salary_type===$t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Base salary *</label>
                <input type="number" step="0.01" min="0" name="base_salary" class="form-control" required value="{{ old('base_salary',$staff->base_salary) }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Join date</label>
                <input type="date" name="join_date" class="form-control" value="{{ optional($staff->join_date)->format('Y-m-d') }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Status *</label>
                <select name="status" class="form-select" required>
                    @foreach(['active','inactive'] as $st)
                        <option value="{{ $st }}" @selected($staff->status===$st)>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <button class="btn btn-primary">Update</button>
                <a href="{{ route('staff.index') }}" class="btn btn-light">Back</a>
            </div>
        </form>
    </div>
@endsection
