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
                                <i class="bi bi-box-arrow-up-right"></i> Issue Book
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
                            <form method="POST" action="{{ route('library.issues.store') }}">
                                @csrf

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Book</label>
                                        <select name="book_id" class="form-select @error('book_id') is-invalid @enderror" required>
                                            <option value="">Select book</option>
                                            @foreach($books as $b)
                                                <option value="{{ $b->id }}" {{ old('book_id')==$b->id ? 'selected' : '' }}>
                                                    {{ $b->title }} (Available: {{ (int)$b->available_quantity }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('book_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Student</label>
                                        <select name="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
                                            <option value="">Select student</option>
                                            @foreach($students as $s)
                                                <option value="{{ $s->id }}" {{ old('student_id')==$s->id ? 'selected' : '' }}>
                                                    {{ $s->first_name }} {{ $s->last_name }} (ID: {{ $s->id }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('student_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Issue Date</label>
                                        <input type="date" name="issue_date" value="{{ old('issue_date', date('Y-m-d')) }}"
                                               class="form-control @error('issue_date') is-invalid @enderror" required>
                                        @error('issue_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Due Date (optional)</label>
                                        <input type="date" name="due_date" value="{{ old('due_date') }}"
                                               class="form-control @error('due_date') is-invalid @enderror">
                                        @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label">Notes (optional)</label>
                                        <textarea name="notes" rows="3"
                                                  class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="bi bi-check2-circle"></i> Issue
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
