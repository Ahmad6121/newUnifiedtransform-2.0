@extends('layouts.app')

@section('content')
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0">
                    Gradebook — {{ $course->course_name }}
                </h4>
                <div class="text-muted">
                    Class:
                    {{ $class ? $class->class_name : ($course->class_id ?? '-') }}
                </div>
            </div>

            <div>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Email</th>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($students as $i => $s)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>
                                {{ $s->display_name ?? ('Student #'.$s->id) }}
                            </td>
                            <td>
                                <small class="text-muted">{{ $s->email ?? '-' }}</small>
                            </td>
                            <td class="text-end">
                                {{-- روابط اختيارية: إذا عندك reportcard routes --}}
                                @if(Route::has('reportcard.child'))
                                    <a class="btn btn-sm btn-outline-dark" href="{{ route('reportcard.child', $s->id) }}">
                                        Report Card
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">No students found for this course/class.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

