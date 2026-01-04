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
                                <i class="bi bi-book"></i> Library Books
                            </h1>
                            <div class="d-flex gap-2">
                                <a href="{{ route('library.issues.create') }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-box-arrow-up-right"></i> Issue Book
                                </a>
                                <a href="{{ route('library.books.create') }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus-circle"></i> Add Book
                                </a>
                            </div>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if (session('status'))
                            <div class="alert alert-success">{{ session('status') }}</div>
                        @endif

                        <div class="p-3 bg-white border shadow-sm mb-3">
                            <form method="GET" action="{{ route('library.books.index') }}" class="row g-2 align-items-end">
                                <div class="col-md-6">
                                    <label class="form-label">Search</label>
                                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Title / Author / ISBN">
                                </div>
                                <div class="col-md-6">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="bi bi-search"></i> Search
                                    </button>
                                    <a class="btn btn-outline-secondary" href="{{ route('library.books.index') }}">
                                        <i class="bi bi-x-circle"></i> Reset
                                    </a>
                                    <a class="btn btn-outline-dark" href="{{ route('library.reports.index') }}">
                                        <i class="bi bi-exclamation-triangle"></i> Overdue Report
                                    </a>
                                </div>
                            </form>
                        </div>

                        <div class="p-3 bg-white border shadow-sm">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>ISBN</th>
                                    <th>Qty</th>
                                    <th>Available</th>
                                    <th style="width:200px;">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($books as $b)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $b->title }}</div>
                                            <div class="small text-muted">
                                                {{ $b->author ?? '-' }} | {{ $b->publisher ?? '-' }} | {{ $b->published_year ?? '-' }}
                                            </div>
                                        </td>
                                        <td>{{ $b->isbn ?? '-' }}</td>
                                        <td>{{ (int)$b->quantity }}</td>
                                        <td>
                                            @if((int)$b->available_quantity > 0)
                                                <span class="badge bg-success">{{ (int)$b->available_quantity }}</span>
                                            @else
                                                <span class="badge bg-danger">0</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a class="btn btn-sm btn-outline-primary"
                                                   href="{{ route('library.books.edit', $b->id) }}">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                <form action="{{ route('library.books.destroy', $b->id) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Delete this book?');">
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
                                        <td colspan="5" class="text-muted">No books found.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>

                            <div class="mt-3">
                                {{ $books->links() }}
                            </div>
                        </div>

                    </div>
                </div>
                @include('layouts.footer')
            </div>
        </div>
    </div>
@endsection
