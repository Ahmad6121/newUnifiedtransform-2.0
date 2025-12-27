@extends('layouts.app')

@section('content')
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Assessments</h4>
            @if(in_array(Auth::user()->role, ['admin','teacher']))
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>            @endif
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Course</th>
                        <th>Class / Section</th>
                        <th>Kind</th>
                        <th>Mode</th>
                        <th>Total</th>
                        <th>Weight%</th>
                        <th>Status</th>
                        <th style="width:320px;">Actions</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($assessments as $a)
                        <tr>
                            <td>{{ $a->id }}</td>
                            <td>{{ $a->title }}</td>

                            <td>{{ $a->course->course_name ?? ($a->course_id ?? '-') }}</td>

                            <td>
                                {{ $a->schoolClass->class_name ?? ($a->class_id ?? '-') }}
                                /
                                {{ $a->section->section_name ?? ($a->section_id ?? '-') }}
                            </td>

                            <td>{{ strtoupper($a->kind) }}</td>
                            <td>{{ strtoupper($a->mode) }}</td>
                            <td>{{ $a->total_marks }}</td>
                            <td>{{ $a->weight_percent }}</td>
                            <td>
                                <span class="badge {{ $a->status === 'published' ? 'bg-success' : ($a->status === 'closed' ? 'bg-dark' : 'bg-secondary') }}">
                                    {{ strtoupper($a->status) }}
                                </span>
                            </td>

                            <td class="d-flex gap-2 flex-wrap">

                                <a class="btn btn-sm btn-outline-secondary"
                                   href="{{ route('assessments.edit', $a->id) }}">Edit</a>

                                @if($a->mode === 'online')
                                    <a class="btn btn-sm btn-outline-primary"
                                       href="{{ route('assessments.questions.index', $a->id) }}">Builder</a>
                                @endif

                                @if(in_array(Auth::user()->role, ['admin','teacher']))
                                    <a class="btn btn-sm btn-outline-dark"
                                       href="{{ route('teacher.assessments.attempts', $a->id) }}">Attempts</a>
                                @endif

                                {{-- Manual marks entry --}}
                                <a class="btn btn-sm btn-outline-warning"
                                   href="{{ route('assessments.marks.edit', $a->id) }}">Marks</a>

                                @if($a->course_id)
                                    <a class="btn btn-sm btn-outline-success"
                                       href="{{ route('gradebook.course', $a->course_id) }}">Gradebook</a>
                                @endif

                                {{-- Publish / Unpublish --}}
                                @if($a->status !== 'published')
                                    <form class="d-inline" method="POST" action="{{ route('assessments.publish', $a->id) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-success">Publish</button>
                                    </form>
                                @else
                                    <form class="d-inline" method="POST" action="{{ route('assessments.unpublish', $a->id) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-secondary">Unpublish</button>
                                    </form>
                                @endif

                                {{-- Close --}}
                                @if($a->status !== 'closed')
                                    <form class="d-inline" method="POST" action="{{ route('assessments.close', $a->id) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-dark">Close</button>
                                    </form>
                                @endif

                                {{-- Delete --}}
                                <form class="d-inline" method="POST" action="{{ route('assessments.destroy', $a->id) }}"
                                      onsubmit="return confirm('Are you sure you want to delete this assessment?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>

                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center py-4">No assessments.</td></tr>
                    @endforelse
                    </tbody>

                </table>
            </div>
        </div>

        <div class="mt-3">{{ $assessments->links() }}</div>
    </div>
@endsection
