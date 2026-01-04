@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Expenses</h3>
            <a class="btn btn-primary" href="{{ route('finance.expenses.create') }}">
                <i class="fas fa-plus"></i> Add Expense
            </a>
        </div>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('finance.expenses.index') }}" class="row g-2">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control"
                               placeholder="Search title/notes..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-dark w-100">Search</button>
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
                            <th class="ps-3">Title</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($expenses as $e)
                            <tr>
                                <td class="ps-3 fw-bold">{{ $e->title }}</td>
                                <td class="text-danger fw-bold">${{ number_format($e->amount, 2) }}</td>
                                <td>{{ $e->expense_date }}</td>
                                <td class="text-end pe-3">
                                    <a class="btn btn-sm btn-outline-primary"
                                       href="{{ route('finance.expenses.edit', $e) }}">Edit</a>

                                    <form action="{{ route('finance.expenses.destroy', $e) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Delete this expense?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-4">No expenses found.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $expenses->links() }}
                </div>
            </div>
        </div>

    </div>
@endsection
