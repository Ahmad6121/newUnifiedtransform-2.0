@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="mb-0">Grade Systems</h2>
            <a class="btn btn-primary" href="{{ route('exam.grade.system.create') }}">+ Add Grade System</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Semester</th>
                            <th>Created</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($systems as $s)
                            <tr>
                                <td>{{ $s->name }}</td>
                                <td>{{ $s->class->{$classNameCol} ?? 'All' }}</td>
                                <td>{{ $s->semester->{$semesterNameCol} ?? 'All' }}</td>
                                <td>{{ $s->created_at }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-muted">No grading systems.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <a class="btn btn-outline-dark" href="{{ route('exam.grade.system.rule.create') }}">+ Add Grade Rule</a>
                    <a class="btn btn-outline-primary" href="{{ route('exam.grade.system.rule.show') }}">View Grade Rules</a>
                </div>
            </div>
        </div>
    </div>
@endsection
