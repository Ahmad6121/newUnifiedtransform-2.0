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
                                <i class="bi bi-people"></i> Staff
                            </h1>
                            <a href="{{ route('staff.employees.create') }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-circle"></i> Add Staff
                            </a>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if (session('status'))
                            <div class="alert alert-success">{{ session('status') }}</div>
                        @endif

                        <div class="p-3 bg-white border shadow-sm mb-3">
                            <form method="GET" action="{{ route('staff.employees.index') }}" class="row g-2 align-items-end">
                                <div class="col-md-6">
                                    <label class="form-label">Search</label>
                                    <input type="text" name="search" value="{{ request('search') }}"
                                           class="form-control" placeholder="Name / Email / Phone">
                                </div>
                                <div class="col-md-6">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="bi bi-search"></i> Search
                                    </button>
                                    <a class="btn btn-outline-secondary" href="{{ route('staff.employees.index') }}">
                                        <i class="bi bi-x-circle"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>

                        <div class="p-3 bg-white border shadow-sm">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Job Title</th>
                                    <th>Salary</th>
                                    <th>Status</th>
                                    <th style="width:200px;">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($staff as $emp)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $emp->first_name }} {{ $emp->last_name }}</div>
                                            <div class="small text-muted">
                                                {{ $emp->email ?? '-' }} | {{ $emp->phone ?? '-' }}
                                            </div>
                                        </td>
                                        <td>{{ optional($emp->jobTitle)->name ?? '-' }}</td>
                                        <td>
                                            {{ strtoupper($emp->salary_type) }} -
                                            {{ number_format((float)$emp->base_salary, 2) }}
                                        </td>
                                        <td>
                                            @if($emp->status === 'active')
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a class="btn btn-sm btn-outline-primary"
                                                   href="{{ route('staff.employees.edit', ['employee' => $emp->id]) }}">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>

                                                <form action="{{ route('staff.employees.destroy', ['employee' => $emp->id]) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Delete this staff member?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger" type="submit">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-muted">No staff records found.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>

                            <div class="mt-3">
                                {{ $staff->links() }}
                            </div>
                        </div>

                    </div>
                </div>
                @include('layouts.footer')
            </div>
        </div>
    </div>
@endsection
