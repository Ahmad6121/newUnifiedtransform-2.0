@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h3 mb-0">Finance Dashboard 📊</h2>

            <form class="d-flex gap-2" method="GET">
                <input type="date" name="from" class="form-control" value="{{ $from ?? '' }}">
                <input type="date" name="to" class="form-control"  value="{{ $to ?? '' }}">
                <button class="btn btn-dark">Apply</button>
            </form>
        </div>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="text-muted">Income</div>
                        <div class="h4 mb-0 text-success">${{ number_format($income,2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="text-muted">Expenses</div>
                        <div class="h4 mb-0 text-danger">${{ number_format($expenses,2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="text-muted">Salaries</div>
                        <div class="h4 mb-0 text-warning">${{ number_format($salaries,2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="text-muted">Net</div>
                        <div class="h4 mb-0">{{ $net >= 0 ? '$'.number_format($net,2) : '-$'.number_format(abs($net),2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold">Recent Expenses</div>
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            <thead><tr><th>Title</th><th>Date</th><th class="text-end">Amount</th></tr></thead>
                            <tbody>
                            @forelse($recentSalaries as $p)
                                <tr>
                                    <td>{{ $p->employee_name ?? 'Employee' }}</td>
                                    <td>{{ $p->month_label ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center py-3">No expenses</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold">Recent Salary Payments</div>
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            <thead><tr><th>Employee</th><th>Month</th><th class="text-end">Amount</th></tr></thead>
                            <tbody>
                            @forelse($recentSalaries as $p)
                                <tr>
                                    <td>{{ $p->user ? trim(($p->user->first_name ?? '').' '.($p->user->last_name ?? '')) : 'Employee' }}</td>
                                    <td>{{ optional($p->salary_month)->format('Y-m') }}</td>
                                    <td class="text-end text-warning">${{ number_format($p->amount,2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center py-3">No salary payments</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
