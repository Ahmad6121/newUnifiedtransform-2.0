@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')

            <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
                <div class="row pt-2">
                    <div class="col ps-4">

                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h1 class="display-6 mb-0">
                                <i class="bi bi-graph-up"></i> Library Reports
                            </h1>

                            <a href="{{ route('library.books.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Books
                            </a>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="p-3 bg-white border shadow-sm rounded">
                                    <div class="fw-bold">Total Books</div>
                                    <div class="fs-3">{{ $totalBooks }}</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="p-3 bg-white border shadow-sm rounded">
                                    <div class="fw-bold">Total Copies</div>
                                    <div class="fs-3">{{ $totalCopies }}</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="p-3 bg-white border shadow-sm rounded">
                                    <div class="fw-bold">Available Copies</div>
                                    <div class="fs-3">{{ $availableCopies }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="p-3 bg-white border shadow-sm rounded">
                                    <div class="fw-bold">Issued</div>
                                    <div class="fs-3">{{ $issuedCount }}</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="p-3 bg-white border shadow-sm rounded">
                                    <div class="fw-bold">Overdue</div>
                                    <div class="fs-3">{{ $overdueCount }}</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="p-3 bg-white border shadow-sm rounded">
                                    <div class="fw-bold">Returned</div>
                                    <div class="fs-3">{{ $returnedCount }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 bg-white border shadow-sm rounded">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h5 class="mb-0">Latest Issues</h5>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Book</th>
                                        <th>Student</th>
                                        <th>Status</th>
                                        <th>Issue Date</th>
                                        <th>Due Date</th>
                                        <th>Return Date</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($latestIssues as $issue)
                                        <tr>
                                            <td>{{ $issue->id }}</td>
                                            <td>{{ optional($issue->book)->title ?? '-' }}</td>
                                            <td>
                                                @php $s = optional($issue->student); @endphp
                                                {{ $s ? ($s->first_name . ' ' . $s->last_name) : '-' }}
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $issue->status }}</span>
                                            </td>
                                            <td>{{ $issue->issue_date }}</td>
                                            <td>{{ $issue->due_date ?? '-' }}</td>
                                            <td>{{ $issue->return_date ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">No issues yet.</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                        </div>

                        @include('layouts.footer')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
