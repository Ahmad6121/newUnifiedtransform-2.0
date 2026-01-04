@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')

            <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
                <div class="row pt-2">
                    <div class="col ps-4">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h1 class="display-6 mb-0">
                                <i class="bi bi-file-earmark-text"></i> Report Card
                            </h1>

                            <div class="d-flex gap-2">
                                @php $role = auth()->user()->role ?? ''; @endphp

                                @if($role === 'parent')
                                    <a class="btn btn-sm btn-outline-primary"
                                       href="{{ \Illuminate\Support\Facades\Route::has('parent.children') ? route('parent.children') : url('/parent/children') }}">
                                        Back to Children
                                    </a>
                                @elseif($role === 'student')
                                    <a class="btn btn-sm btn-outline-primary"
                                       href="{{ \Illuminate\Support\Facades\Route::has('reportcard.my') ? route('reportcard.my') : url('/report-card/my') }}">
                                        Back
                                    </a>
                                @else
                                    <a class="btn btn-sm btn-outline-primary" href="{{ url()->previous() }}">Back</a>
                                @endif
                            </div>
                        </div>

                        <div class="card shadow-sm mb-3">
                            <div class="card-body">
                                <div class="row g-2">
                                    <div class="col-md-8">
                                        <div class="fw-semibold">Student</div>
                                        <div>
                                            {{ $student->first_name ?? '' }} {{ $student->last_name ?? '' }}
                                            <span class="text-muted">(#{{ $student->id }})</span>
                                        </div>
                                        <div class="text-muted small">{{ $student->email ?? '' }}</div>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <div class="fw-semibold">Overall</div>
                                        <div class="h4 mb-0">{{ number_format((float)($overall ?? 0), 2) }}%</div>
                                    </div>
                                </div>

                                @if(!empty($note))
                                    <div class="alert alert-warning mt-3 mb-0">
                                        {{ $note }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        @forelse($rows as $row)
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <div class="fw-bold">{{ $row['course'] ?? 'Course' }}</div>
                                    <div class="badge bg-dark">
                                        Final: {{ number_format((float)($row['final'] ?? 0), 2) }}%
                                    </div>
                                </div>

                                <div class="card-body p-0">
                                    <table class="table mb-0">
                                        <thead class="table-light">
                                        <tr>
                                            <th>Assessment</th>
                                            <th>Type</th>
                                            <th class="text-end">Mark</th>
                                            <th class="text-end">Total</th>
                                            <th class="text-end">Weight %</th>
                                            <th class="text-end">Percent</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach(($row['items'] ?? []) as $it)
                                            <tr>
                                                <td>{{ $it['title'] ?? '-' }}</td>
                                                <td class="text-muted">{{ $it['kind'] ?? '-' }}</td>
                                                <td class="text-end">{{ number_format((float)($it['mark'] ?? 0), 2) }}</td>
                                                <td class="text-end">{{ number_format((float)($it['total'] ?? 0), 2) }}</td>
                                                <td class="text-end">{{ number_format((float)($it['weight'] ?? 0), 2) }}</td>
                                                <td class="text-end">{{ number_format((float)($it['percent'] ?? 0), 2) }}%</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-info">
                                No published results available for this student in the current session.
                            </div>
                        @endforelse

                    </div>
                </div>

                @include('layouts.footer')
            </div>
        </div>
    </div>
@endsection
