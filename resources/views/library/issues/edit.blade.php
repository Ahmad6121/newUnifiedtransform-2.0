@extends('layouts.app')
@section('content')
    <div class="container">
        <h3>Edit Book Issue</h3>

        <form method="POST" action="{{ route('library.issues.update', $issue) }}" class="row g-3">
            @csrf
            @method('PUT')

            <div class="col-md-6">
                <label class="form-label">Book</label>
                <input type="text" class="form-control" value="{{ $issue->book->title }}" disabled>
            </div>

            <div class="col-md-6">
                <label class="form-label">Student</label>
                <input type="text" class="form-control" value="{{ $issue->student->first_name }} {{ $issue->student->last_name }}" disabled>
            </div>

            <div class="col-md-4">
                <label class="form-label">Issue Date</label>
                <input type="date" name="issue_date" value="{{ $issue->issue_date->format('Y-m-d') }}" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Due Date</label>
                <input type="date" name="due_date" value="{{ optional($issue->due_date)->format('Y-m-d') }}" class="form-control">
            </div>

            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="issued" {{ $issue->status === 'issued' ? 'selected' : '' }}>Issued</option>
                    <option value="returned" {{ $issue->status === 'returned' ? 'selected' : '' }}>Returned</option>
                    <option value="overdue" {{ $issue->status === 'overdue' ? 'selected' : '' }}>Overdue</option>
                </select>
            </div>

            <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3">{{ $issue->notes }}</textarea>
            </div>

            <div class="col-12">
                <button class="btn btn-primary">Update</button>
                <a href="{{ route('library.issues.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
@endsection
