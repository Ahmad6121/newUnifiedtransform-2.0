@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="mb-0">View Exam Rules</h2>
            <a class="btn btn-outline-primary" href="{{ route('exam.rule.create') }}">+ Add Rule</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif

        {{-- Pick exam --}}
        <form method="GET" action="{{ route('exam.rule.show') }}" class="row g-2 mb-3">
            <div class="col-md-8">
                <select class="form-select" name="exam_id" required>
                    <option value="">Select Exam</option>
                    @foreach($exams as $e)
                        <option value="{{ $e->id }}" {{ (string)$exam_id === (string)$e->id ? 'selected' : '' }}>
                            {{ $e->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <button class="btn btn-primary w-100">
                    <i class="bi bi-arrow-clockwise me-1"></i> Load
                </button>
            </div>
        </form>

        <div class="card">
            <div class="card-body">
                @if(!$exam_id)
                    <div class="text-muted">Select an exam to show its rules.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                            <tr>
                                <th>Exam</th>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Created</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($rules as $r)
                                <tr>
                                    <td>{{ $r->exam->name ?? '-' }}</td>
                                    <td>{{ $r->class->{$classNameCol} ?? '-' }}</td>
                                    <td>{{ $r->section->{$sectionNameCol} ?? '-' }}</td>
                                    <td>{{ $r->created_at }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-muted">No rules found for this exam.</td>
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

