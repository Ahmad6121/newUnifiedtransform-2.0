@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Books</h3>
            <a href="{{ route('library.books.create') }}" class="btn btn-primary">Add Book</a>
        </div>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>ISBN</th>
                    <th>Qty</th>
                    <th>Avail</th>
                    <th>Shelf</th>
                    <th>Publisher</th>
                    <th>Year</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($books as $b)
                    <tr>
                        <td>{{ $b->id }}</td>
                        <td>{{ $b->title }}</td>
                        <td>{{ $b->author ?? '-' }}</td>
                        <td>{{ $b->isbn ?? '-' }}</td>
                        <td>{{ $b->quantity }}</td>
                        <td>{{ $b->available_quantity }}</td>
                        <td>{{ $b->shelf ?? '-' }}</td>
                        <td>{{ $b->publisher ?? '-' }}</td>
                        <td>{{ $b->published_year ?? '-' }}</td>
                        <td class="text-end d-flex gap-1 justify-content-end">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('library.books.edit', $b) }}">Edit</a>
                            <form method="POST" action="{{ route('library.books.destroy', $b) }}" onsubmit="return confirm('Delete book?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center">No books</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $books->links() }}
    </div>
@endsection
