@extends('layouts.app')

@section('content')
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0">Enter Marks</h4>
                <div class="text-muted">
                    Assessment: <strong>{{ $assessment->title ?? ('#'.$assessment->id) }}</strong>
                </div>
            </div>
            <a class="btn btn-outline-secondary" href="{{ route('assessments.index') }}">Back</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('assessments.marks.update', $assessment->id) }}">
            @csrf

            <div class="card">
                <div class="card-body">

                    <div class="mb-3">
                        <span class="badge bg-dark">Total Marks: {{ $assessment->total_marks }}</span>
                        <span class="badge bg-secondary">Mode: {{ strtoupper($assessment->mode) }}</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                            <tr>
                                <th style="width:110px;">Student ID</th>
                                <th>Student</th>
                                <th style="width:220px;">Marks Obtained</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($students as $s)
                                @php
                                    // إذا عندك مصفوفة marks جاهزة من الكنترولر
                                    $val = $marks[$s->id] ?? null;
                                @endphp
                                <tr>
                                    <td>{{ $s->id }}</td>
                                    <td>{{ $displayUserName($s) ?? ($s->email ?? '-') }}</td>
                                    <td>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="{{ $assessment->total_marks ?? 100 }}"
                                            name="marks[{{ $s->id }}]"
                                            value="{{ $resultsMap[$s->id]->marks_obtained ?? '' }}"
                                            class="form-control"
                                        />

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4">No students found for this assessment scope.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>

                <div class="card-footer text-end">
                    <button class="btn btn-dark">Save Marks</button>
                </div>
            </div>
        </form>

    </div>
@endsection
