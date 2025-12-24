@extends('layouts.app')

@section('content')
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0">Gradebook</h4>
                <div class="text-muted">Select a course to view students</div>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Course</th>
                        <th>Type</th>
                        <th>Class</th>
                        <th class="text-end">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($courses as $i => $c)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $c->course_name }}</td>
                            <td>{{ $c->course_type ?? '-' }}</td>
                            <td>{{ $c->class_id ?? '-' }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-dark" href="{{ route('gradebook.course', $c->id) }}">
                                    Open
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">No courses found for this session.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection


