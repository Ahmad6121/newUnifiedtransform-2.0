@extends('layouts.app')

@section('content')
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0">Assessments Dashboard</h4>
                <div class="text-muted">Create, publish, enter marks, and publish results</div>
            </div>

            @if(in_array(Auth::user()->role, ['admin','teacher']))
                <a class="btn btn-dark" href="{{ route('assessments.create') }}">+ Create Assessment</a>
            @endif
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Summary Cards --}}
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted">Total</div>
                        <div class="fs-4 fw-bold">{{ $total ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted">Published</div>
                        <div class="fs-4 fw-bold">{{ $published ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted">Results Visible</div>
                        <div class="fs-4 fw-bold">{{ $resultsPublished ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted">Upcoming</div>
                        <div class="fs-4 fw-bold">{{ $upcoming ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Kind</th>
                        <th>Mode</th>
                        <th>Class</th>
                        <th>Course</th>
                        <th>Total</th>
                        <th>Weight</th>
                        <th>Status</th>
                        <th>Results</th>
                        <th>Graded</th>
                        <th>Avg%</th>
                        <th>Pass%</th>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse(($rows ?? []) as $i => $row)
                        @php $a = $row['a']; @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="fw-semibold">{{ $a->title }}</td>
                            <td>{{ strtoupper($a->kind) }}</td>
                            <td>{{ strtoupper($a->mode) }}</td>
                            <td>{{ $row['class_name'] ?? '-' }}</td>
                            <td>{{ $row['course_name'] ?? '-' }}</td>
                            <td>{{ $a->total_marks }}</td>
                            <td>{{ $a->weight_percent ?? 0 }}%</td>

                            <td>
                            <span class="badge {{ $a->status == 'published' ? 'bg-success' : 'bg-secondary' }}">
                                {{ strtoupper($a->status) }}
                            </span>
                            </td>

                            <td>
                            <span class="badge {{ $a->results_published ? 'bg-primary' : 'bg-warning text-dark' }}">
                                {{ $a->results_published ? 'VISIBLE' : 'HIDDEN' }}
                            </span>
                            </td>

                            <td>{{ $row['graded'] ?? 0 }}</td>
                            <td>{{ $row['avg_percent'] ?? 0 }}</td>
                            <td>{{ $row['pass_rate'] ?? 0 }}</td>

                            <td class="text-end">

                                {{-- Enter Marks (Manual) --}}
                                <a class="btn btn-sm btn-outline-dark"
                                   href="{{ route('assessments.marks.edit', $a->id) }}">
                                    Enter Marks
                                </a>

                                {{-- Publish --}}
                                @if($a->status !== 'published')
                                    <form class="d-inline" method="POST" action="{{ route('assessments.publish', $a->id) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-success">Publish</button>
                                    </form>
                                @endif

                                {{-- Publish Results --}}
                                @if(!$a->results_published)
                                    <form class="d-inline" method="POST" action="{{ route('assessments.results.publish', $a->id) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-primary">Publish Results</button>
                                    </form>
                                @endif

                                <a class="btn btn-sm btn-dark" href="{{ route('reports.assessment.csv', $a->id) }}">CSV</a>
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('reports.assessment.pdf', $a->id) }}">PDF</a>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="text-center py-4">No assessments yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
