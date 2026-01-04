@php
    $u = auth()->user();
    $role = $u ? ($u->role ?? '') : '';
    $isAdmin = $u && (method_exists($u,'isAdmin') ? $u->isAdmin() : ($role === 'admin'));
    $isAccountant = $u && ($role === 'accountant');
@endphp

@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 mb-0 text-gray-800">Invoice Management 💳</h2>
            <div class="d-flex gap-2">
                @if($isAdmin || $isAccountant)
                <a href="{{ route('finance.dashboard') }}" class="btn btn-outline-dark">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
                @endif
                @if($isAdmin || $isAccountant)

                <a href="{{ route('finance.expenses.index') }}" class="btn btn-outline-danger">
                    <i class="fas fa-receipt"></i> Expenses
                </a>
                @endif
                @if($isAdmin || $isAccountant)
                <a href="{{ route('finance.payroll.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-wallet"></i> Payroll
                </a>
                @endif
            </div>


            {{-- زر الإنشاء يظهر للآدمن والمحاسب فقط --}}
            @if($isAdmin || $isAccountant)
                <a href="{{ route('finance.invoices.create') }}" class="btn btn-primary shadow-sm">
                    <i class="fas fa-plus fa-sm"></i> Create New Invoice
                </a>

            @endif
        </div>

        @if(session('status'))
            <div class="alert alert-success shadow-sm border-0">
                <i class="fas fa-check-circle me-2"></i> {{ session('status') }}
            </div>
        @endif

        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body">
                <form action="{{ route('finance.invoices.index') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="small fw-bold">Search Student</label>
                        <input type="text" name="search" class="form-control" placeholder="Enter name..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid ✅</option>
                            <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial ⚠️</option>
                            <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid ❌</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-dark w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                        <tr>
                            <th class="ps-3">Inv #</th>
                            <th>Student</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                            @if($isAdmin || $isAccountant)
                                <th>Quick Payment</th>
                            @endif
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($invoices as $invoice)
                            <tr>
                                <td class="ps-3 text-muted">#{{ $invoice->invoice_number }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $invoice->student->first_name }} {{ $invoice->student->last_name }}</div>
                                    <small class="text-muted">{{ $invoice->title }}</small>
                                </td>
                                <td class="fw-bold text-primary">${{ number_format($invoice->amount, 2) }}</td>
                                <td class="text-success">${{ number_format($invoice->paid_amount, 2) }}</td>
                                <td class="text-danger fw-bold">${{ number_format($invoice->amount - $invoice->paid_amount, 2) }}</td>
                                <td>
                                    @php $badge = ['unpaid'=>'danger','partial'=>'warning text-dark','paid'=>'success'][$invoice->status] ?? 'secondary'; @endphp
                                    <span class="badge bg-{{ $badge }}">{{ strtoupper($invoice->status) }}</span>
                                </td>

                                @if($isAdmin || $isAccountant)
                                    <td>
                                        <form method="POST" action="{{ route('finance.invoices.quickPayment', $invoice->id) }}" class="d-flex gap-1">
                                            @csrf
                                            <input type="number" step="0.01" name="amount" placeholder="Amt" class="form-control form-control-sm" style="width:70px" required>
                                            <button type="submit" class="btn btn-sm btn-success px-2">Pay</button>
                                        </form>
                                    </td>
                                @endif

                                <td class="text-end pe-3">
                                    <div class="btn-group">
                                        <a href="{{ route('finance.invoices.print', $invoice->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-print"></i> Print
                                        </a>
                                        @if($isAdmin || $isAccountant)
                                            <a href="{{ route('finance.invoices.edit', $invoice->id) }}" class="btn btn-sm btn-outline-primary ms-1">Edit</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-4">No invoices found.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
