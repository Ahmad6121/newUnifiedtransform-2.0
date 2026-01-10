@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h3 mb-0">My Salary 💰</h2>

            <form method="GET" class="d-flex gap-2">
                <input type="date" name="month" class="form-control" value="{{ $month ?? now()->format('Y-m-01') }}">
                <button class="btn btn-dark">Load</button>
            </form>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="text-muted">Base Salary</div>
                        <div class="h4 mb-0">${{ number_format((float)($baseSalary ?? 0), 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="text-muted">Paid This Month</div>
                        <div class="h4 mb-0 text-success">${{ number_format((float)($paidThisMonth ?? 0), 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="text-muted">Month</div>
                        <div class="h4 mb-0">{{ \Carbon\Carbon::parse($month ?? now())->format('M Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold">Salary Payments</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead class="bg-light">
                        <tr>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Paid At</th>
                            <th class="text-end">Amount</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse(($rows ?? collect()) as $r)
                            <tr>
                                <td class="fw-bold">{{ $r->title ?? 'Salary' }}</td>
                                <td>{{ $r->date_label ?? '-' }}</td>
                                <td>{{ $r->paid_at_label ?? '-' }}</td>
                                <td class="text-end text-success">
                                    ${{ number_format((float)($r->amount ?? 0), 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">No salary payments for this month.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection
