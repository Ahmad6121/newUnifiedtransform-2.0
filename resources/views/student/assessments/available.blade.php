@extends('layouts.app')

@section('content')
    <div class="container">
        <h4 class="mb-3">My Available Exams</h4>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Kind</th>
                        <th>Mode</th>
                        <th>Total</th>
                        <th>Dates</th>
                        <th style="width:160px;">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($assessments as $a)
                        <tr>
                            <td>{{ $a->id }}</td>
                            <td>{{ $a->title }}</td>
                            <td>{{ strtoupper($a->kind) }}</td>
                            <td>{{ strtoupper($a->mode) }}</td>
                            <td>{{ $a->total_marks }}</td>
                            <td>
                                <div>Start: {{ $a->start_date? $a->start_date->format('Y-m-d H:i') : '-' }}</div>
                                <div>End: {{ $a->end_date? $a->end_date->format('Y-m-d H:i') : '-' }}</div>
                            </td>
                            <td>
                                <a class="btn btn-sm btn-primary" href="{{ route('student.assessments.start', $a->id) }}">
                                    Start
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4">No published exams.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">{{ $assessments->links() }}</div>
    </div>
@endsection
