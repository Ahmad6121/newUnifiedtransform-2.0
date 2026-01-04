@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')

            <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
                <div class="row pt-2">
                    <div class="col ps-4">

                        @php
                            $u = auth()->user();
                            $isAdmin = $u && $u->role === 'admin';
                        @endphp

                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h1 class="display-6 mb-0">
                                <i class="bi bi-cash-coin"></i> Accountants
                            </h1>

                            {{-- ✅ Admin فقط يشوف زر الإضافة --}}
                            @if($isAdmin)
                                <a href="{{ route('accountants.create') }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus-circle"></i> Add Accountant
                                </a>
                            @endif
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if (session('status'))
                            <div class="alert alert-success">{{ session('status') }}</div>
                        @endif

                        <div class="p-3 bg-white border shadow-sm mb-3">
                            <form method="GET" action="{{ route('accountants.index') }}" class="row g-2 align-items-end">
                                <div class="col-md-6">
                                    <label class="form-label">Search</label>
                                    <input type="text" name="search" value="{{ request('search') }}"
                                           class="form-control" placeholder="Name / Email / Phone">
                                </div>

                                <div class="col-md-6">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="bi bi-search"></i> Search
                                    </button>
                                    <a class="btn btn-outline-secondary" href="{{ route('accountants.index') }}">
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
                                    <th>Email</th>
                                    <th>Phone</th>

                                    {{-- ✅ Actions بس للأدمن --}}
                                    @if($isAdmin)
                                        <th style="width:200px;">Actions</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody>

                                @forelse(($accountants ?? []) as $acc)
                                    <tr>
                                        <td class="fw-semibold">{{ $acc->first_name }} {{ $acc->last_name }}</td>
                                        <td>{{ $acc->email ?? '-' }}</td>
                                        <td>{{ $acc->phone ?? '-' }}</td>

                                        @if($isAdmin)
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a class="btn btn-sm btn-outline-primary"
                                                       href="{{ route('accountants.edit', $acc->id) }}">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </a>

                                                    <form action="{{ route('accountants.destroy', $acc->id) }}"
                                                          method="POST"
                                                          onsubmit="return confirm('Delete this accountant?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger" type="submit">
                                                            <i class="bi bi-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $isAdmin ? 4 : 3 }}" class="text-muted">
                                            No accountants found.
                                        </td>
                                    </tr>
                                @endforelse

                                </tbody>
                            </table>

                            {{-- ✅ Pagination فقط لو Pagination --}}
                            @if(isset($accountants) && method_exists($accountants, 'links'))
                                <div class="mt-3">
                                    {{ $accountants->links() }}
                                </div>
                            @endif
                        </div>

                    </div>
                </div>

                @include('layouts.footer')
            </div>
        </div>
    </div>
@endsection
