@extends('layouts.app')

@section('content')
    <div class="container">

        {{-- Alerts --}}
        @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
        @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
        @if(session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0">Attempts</h4>
                <div class="text-muted">
                    Assessment: <strong>{{ $assessment->title ?? '—' }}</strong>
                    <span class="ms-2 badge bg-secondary">{{ strtoupper($assessment->kind ?? '-') }}</span>
                    <span class="ms-1 badge bg-info text-dark">{{ strtoupper($assessment->mode ?? '-') }}</span>
                    <span class="ms-1 badge {{ ($assessment->status ?? '') === 'published' ? 'bg-success' : 'bg-secondary' }}">
                        {{ strtoupper($assessment->status ?? '-') }}
                    </span>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a class="btn btn-outline-secondary" href="{{ route('assessments.index') }}">Back</a>

                {{-- Publish / Unpublish / Close --}}
                @if(($assessment->status ?? 'draft') !== 'published')
                    <form method="POST" action="{{ route('assessments.publish', $assessment->id) }}">
                        @csrf
                        <button class="btn btn-success">Publish</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('assessments.unpublish', $assessment->id) }}">
                        @csrf
                        <button class="btn btn-outline-secondary">Unpublish</button>
                    </form>
                @endif

                @if(($assessment->status ?? '') !== 'closed')
                    <form method="POST" action="{{ route('assessments.close', $assessment->id) }}">
                        @csrf
                        <button class="btn btn-dark">Close</button>
                    </form>
                @endif

                {{-- Results Publish/Hide (FIXED ROUTES) --}}
                @if(!($assessment->results_published ?? false))
                    <form method="POST" action="{{ route('assessments.results.publish', $assessment->id) }}">
                        @csrf
                        <button class="btn btn-primary">Publish Results</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('assessments.results.hide', $assessment->id) }}">
                        @csrf
                        <button class="btn btn-warning">Hide Results</button>
                    </form>
                @endif

                {{-- Exports --}}
                <a class="btn btn-outline-success" href="{{ route('reports.assessment.csv', $assessment->id) }}">CSV</a>
                <a class="btn btn-outline-danger" href="{{ route('reports.assessment.pdf', $assessment->id) }}">PDF</a>
            </div>
        </div>

        {{-- Attempts table --}}
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                        <tr>
                            <th style="width:80px;">#</th>
                            <th>Student</th>
                            <th style="width:120px;">Marks</th>
                            <th style="width:120px;">Percent</th>
                            <th style="width:140px;">Status</th>
                            <th style="width:180px;">Submitted</th>
                            <th class="text-end" style="width:180px;">Actions</th>
                        </tr>
                        </thead>
                        <tbody>

                        @php
                            // Support both variable names: $attempts or $rows
                            $attemptRows = $attempts ?? ($rows ?? collect());
                        @endphp

                        @forelse($attemptRows as $i => $attempt)
                            @php
                                // Student name best-effort
                                $student = $attempt->student ?? null;
                                $studentName =
                                    ($student->name ?? null)
                                    ?: trim(($student->first_name ?? '').' '.($student->last_name ?? ''))
                                    ?: ($student->email ?? null)
                                    ?: ('Student #'.($attempt->student_id ?? '—'));

                                $marks = $attempt->marks_obtained
                                    ?? $attempt->marks
                                    ?? $attempt->score
                                    ?? null;

                                $total = (float)($assessment->total_marks ?? 100);

                                $percent = null;
                                if ($marks !== null && $total > 0) {
                                    $percent = round(((float)$marks / $total) * 100, 2);
                                }

                                $status = $attempt->status
                                    ?? ($attempt->is_submitted ?? false ? 'submitted' : 'in_progress');

                                $submittedAt = $attempt->submitted_at ?? $attempt->end_time ?? $attempt->updated_at ?? null;

                                $badge =
                                    in_array(strtolower($status), ['submitted','completed','finished']) ? 'bg-success'
                                    : (in_array(strtolower($status), ['in_progress','started']) ? 'bg-warning text-dark'
                                    : 'bg-secondary');
                            @endphp

                            <tr>
                                <td>{{ is_numeric($i) ? ($i+1) : $attempt->id }}</td>

                                <td>
                                    <div class="fw-semibold">{{ $studentName }}</div>
                                    <div class="text-muted small">
                                        ID: {{ $attempt->student_id ?? ($student->id ?? '-') }}
                                    </div>
                                </td>

                                <td>
                                    @if($marks === null)
                                        <span class="text-muted">—</span>
                                    @else
                                        {{ $marks }} / {{ (int)$total }}
                                    @endif
                                </td>

                                <td>
                                    @if($percent === null)
                                        <span class="text-muted">—</span>
                                    @else
                                        {{ $percent }}%
                                    @endif
                                </td>

                                <td>
                                    <span class="badge {{ $badge }}">
                                        {{ strtoupper((string)$status) }}
                                    </span>
                                </td>

                                <td>
                                    @if($submittedAt)
                                        <span>{{ \Carbon\Carbon::parse($submittedAt)->format('Y-m-d H:i') }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-dark"
                                       href="{{ route('teacher.assessments.review', $attempt->id) }}">
                                        Review / Grade
                                    </a>

                                    @if(($assessment->mode ?? '') === 'manual')
                                        <a class="btn btn-sm btn-outline-primary"
                                           href="{{ route('assessments.marks.edit', $assessment->id) }}">
                                            Enter Marks
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    No attempts found for this assessment.
                                </td>
                            </tr>
                        @endforelse

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection
