@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h3 mb-0">Payroll 💼</h2>

            <form method="GET" class="d-flex gap-2">
                <input type="date" name="month" class="form-control" value="{{ $month ?? now()->format('Y-m-01') }}">
                <button class="btn btn-dark">Load</button>
            </form>
        </div>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead class="bg-light">
                        <tr>
                            <th>Employee</th>
                            <th>Role</th>
                            <th>Base Salary</th>
                            <th>Paid This Month</th>
                            <th>Pay Now</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($employees as $emp)
                            <tr>
                                <td class="fw-bold">{{ $emp['name'] }}</td>
                                <td>{{ $emp['role'] }}</td>

                                <td style="min-width:240px">
                                    <form method="POST" action="{{ route('finance.payroll.setSalary') }}" class="d-flex gap-2">
                                        @csrf
                                        <input type="hidden" name="employee_ref" value="{{ $emp['ref'] }}">
                                        <input type="number" step="0.01" min="0" name="base_salary"
                                               class="form-control form-control-sm"
                                               value="{{ (float)($emp['base_salary'] ?? 0) }}">
                                        <button class="btn btn-sm btn-outline-primary">Save</button>
                                    </form>
                                </td>

                                <td class="text-success">${{ number_format((float)$emp['paid'], 2) }}</td>

                                <td style="min-width:360px">
                                    <form method="POST" action="{{ route('finance.payroll.pay') }}" class="d-flex gap-2">
                                        @csrf
                                        <input type="hidden" name="employee_ref" value="{{ $emp['ref'] }}">
                                        <input type="hidden" name="salary_month" value="{{ $month }}">
                                        <input type="number" step="0.01" min="0.01" name="amount"
                                               class="form-control form-control-sm" placeholder="Amount" required>
                                        <button class="btn btn-sm btn-success">Pay</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-4">No employees found</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
