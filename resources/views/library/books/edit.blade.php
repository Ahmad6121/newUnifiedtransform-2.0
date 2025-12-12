@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Edit Book</h3>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('library.books.update', $book) }}" class="row g-3">
            @csrf
            @method('PUT')

            <div class="col-md-6">
                <label class="form-label">Title *</label>
                <input name="title" class="form-control" required value="{{ old('title', $book->title) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">Author</label>
                <input name="author" class="form-control" value="{{ old('author', $book->author) }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">ISBN</label>
                <input name="isbn" class="form-control" value="{{ old('isbn', $book->isbn) }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Quantity *</label>
                <input type="number" min="0" name="quantity" class="form-control" required value="{{ old('quantity', $book->quantity) }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Available</label>
                <input type="number" min="0" name="available_quantity" class="form-control" value="{{ old('available_quantity', $book->available_quantity) }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Shelf</label>
                <input name="shelf" class="form-control" value="{{ old('shelf', $book->shelf) }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Publisher</label>
                <input name="publisher" class="form-control" value="{{ old('publisher', $book->publisher) }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Year</label>
                <input type="number" name="published_year" class="form-control" value="{{ old('published_year', $book->published_year) }}">
            </div>

            <div class="col-12">
                <button class="btn btn-success">Update</button>
                <a href="{{ route('library.books.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
