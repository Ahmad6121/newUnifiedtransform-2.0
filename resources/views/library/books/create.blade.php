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
                                <i class="bi bi-plus-circle"></i> Add Book
                            </h1>
                            <a href="{{ route('library.books.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back
                            </a>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="p-3 bg-white border shadow-sm">
                            <form method="POST" action="{{ route('library.books.store') }}">
                                @csrf

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Title</label>
                                        <input type="text" name="title" value="{{ old('title') }}"
                                               class="form-control @error('title') is-invalid @enderror" required>
                                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Author (optional)</label>
                                        <input type="text" name="author" value="{{ old('author') }}"
                                               class="form-control @error('author') is-invalid @enderror">
                                        @error('author') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">ISBN (optional)</label>
                                        <input type="text" name="isbn" value="{{ old('isbn') }}"
                                               class="form-control @error('isbn') is-invalid @enderror">
                                        @error('isbn') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" min="0" name="quantity" value="{{ old('quantity', 0) }}"
                                               class="form-control @error('quantity') is-invalid @enderror" required>
                                        @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Published Year (optional)</label>
                                        <input type="number" min="0" max="2100" name="published_year" value="{{ old('published_year') }}"
                                               class="form-control @error('published_year') is-invalid @enderror">
                                        @error('published_year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Publisher (optional)</label>
                                        <input type="text" name="publisher" value="{{ old('publisher') }}"
                                               class="form-control @error('publisher') is-invalid @enderror">
                                        @error('publisher') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Shelf (optional)</label>
                                        <input type="text" name="shelf" value="{{ old('shelf') }}"
                                               class="form-control @error('shelf') is-invalid @enderror">
                                        @error('shelf') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="bi bi-check2-circle"></i> Save
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
                @include('layouts.footer')
            </div>
        </div>
    </div>
@endsection
