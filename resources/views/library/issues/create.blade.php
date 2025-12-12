@extends('layouts.app')
@section('content')
    <div class="container">
        <h3>Issue Book</h3>

        <form method="POST" action="{{ route('library.issues.store') }}" class="row g-3">
            @csrf

            <div class="col-md-6">
                <label class="form-label">Select Book</label>
                <select name="book_id" class="form-select" required>
                    <option value="">-- choose book --</option>
                    @foreach($books as $book)
                        <option value="{{ $book->id }}">{{ $book->title }} (Available: {{ $book->available_quantity }})</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Select Student</label>
                <select name="student_id" class="form-select" required>
                    <option value="">-- choose student --</option>
                    @foreach($students as $s)
                        <option value="{{ $s->id }}">{{ $s->first_name }} {{ $s->last_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Issue Date</label>
                <input type="date" name="issue_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Due Date</label>
                <input type="date" name="due_date" class="form-control">
            </div>

            <div class="col-md-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3"></textarea>
            </div>

            <div class="col-12">
                <button class="btn btn-primary">Save</button>
                <a href="{{ route('library.issues.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
@endsection
