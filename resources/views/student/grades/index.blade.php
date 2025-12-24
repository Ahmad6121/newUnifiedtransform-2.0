@extends('layouts.app')
@section('content')
    <div class="container">
        <h4 class="mb-3">My Grades</h4>

        @if(!empty($warning))
            <div class="alert alert-warning">{{ $warning }}</div>
        @endif

        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Course</th>
                        <th>Published Assessments</th>
                        <th>Final /100</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($rows as $i => $r)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $r['course']->course_name }}</td>
                            <td>{{ $r['assessments_count'] }}</td>
                            <td><strong>{{ $r['final'] }}</strong></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-4">No grades yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            <a class="btn btn-outline-primary" href="{{ route('student.report.card') }}">Open Report Card</a>
        </div>
    </div>
@endsection
