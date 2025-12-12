@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Staff</h3>
            <a href="{{ route('staff.create') }}" class="btn btn-primary">Add Staff</a>
        </div>

        @if(session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Job</th><th>Salary</th><th>Type</th><th>Status</th><th>Join</th><th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($staff as $s)
                    <tr>
                        <td>{{ $s->id }}</td>
                        <td>{{ $s->full_name }}</td>
                        <td>{{ $s->email ?? '-' }}</td>
                        <td>{{ $s->phone ?? '-' }}</td>
                        <td>{{ $s->job_title }}</td>
                        <td>{{ number_format($s->base_salary,2) }}</td>
                        <td>{{ $s->salary_type }}</td>
                        <td><span class="badge bg-{{ $s->status==='active'?'success':'secondary' }}">{{ $s->status }}</span></td>
                        <td>{{ optional($s->join_date)->format('Y-m-d') }}</td>
                        <td class="text-end d-flex gap-1 justify-content-end">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('staff.edit',$s) }}">Edit</a>
                            <form method="POST" action="{{ route('staff.destroy',$s) }}" onsubmit="return confirm('Delete staff?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center">No staff</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $staff->links() }}
    </div>
@endsection
