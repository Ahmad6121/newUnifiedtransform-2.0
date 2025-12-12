@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Invoices</h3>
            <a href="{{ route('finance.invoices.create') }}" class="btn btn-primary">Create Invoice</a>
        </div>

        @if(session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Class</th>
                    <th>Title</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Due</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($invoices as $inv)
                    <tr>
                        <td>{{ $inv->id }}</td>
                        <td>{{ optional($inv->student)->first_name }} {{ optional($inv->student)->last_name }}</td>
                        <td>{{ optional($inv->class)->class_name ?? '-' }}</td>
                        <td>{{ $inv->title }}</td>
                        <td>{{ number_format($inv->amount,2) }}</td>
                        <td>
                            @php $badge = ['unpaid'=>'warning','partial'=>'info','paid'=>'success','overdue'=>'danger'][$inv->status] ?? 'secondary'; @endphp
                            <span class="badge bg-{{ $badge }}">{{ $inv->status }}</span>
                        </td>
                        <td>{{ optional($inv->due_date)->format('Y-m-d') }}</td>
                        <td class="text-end d-flex gap-1 justify-content-end">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('finance.invoices.edit',$inv) }}">Edit</a>
                            <form method="POST" action="{{ route('finance.invoices.destroy',$inv) }}" onsubmit="return confirm('Delete invoice?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </form>

                            {{-- Quick Payment --}}
                            <form method="POST" action="{{ route('finance.invoices.payments.store',$inv) }}" class="d-flex gap-1">
                                @csrf
                                <input type="number" step="0.01" min="0.01" name="amount" placeholder="Pay" class="form-control form-control-sm" style="width:110px">
                                <select name="method" class="form-select form-select-sm" style="width:120px">
                                    <option value="cash">cash</option>
                                    <option value="card">card</option>
                                    <option value="transfer">transfer</option>
                                    <option value="online">online</option>
                                </select>
                                <button class="btn btn-sm btn-success">Add</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center">No invoices</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $invoices->links() }}
    </div>
@endsection
