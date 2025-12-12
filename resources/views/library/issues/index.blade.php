@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Book Issues</h3>
            <a href="{{ route('library.issues.create') }}" class="btn btn-primary">Issue Book</a>
        </div>

        <table class="table table-striped">
            <thead><tr><th>#</th><th>Book</th><th>Student</th><th>Issued</th><th>Return</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($issues as $i)
                <tr>
                    <td>{{ $i->id }}</td>
                    <td>{{ $i->book->title }}</td>
                    <td>{{ $i->student->first_name }} {{ $i->student->last_name }}</td>
                    <td>{{ $i->issue_date }}</td>
                    <td>{{ $i->return_date ?? '-' }}</td>
                    <td>{{ ucfirst($i->status) }}</td>
                    <td>
                        @if($i->status==='issued')
                            <form action="{{ route('library.issues.return',$i) }}" method="POST">
                                @csrf
                                <button class="btn btn-sm btn-success">Mark Returned</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">No issues</td></tr>
            @endforelse
            </tbody>
        </table>

        {{ $issues->links() }}
    </div>
@endsection
