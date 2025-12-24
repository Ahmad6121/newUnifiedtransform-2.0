@extends('layouts.app')

@section('content')
    <div class="container">
        @if(session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif
        @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Review Attempt #{{ $attempt->id }}</h4>
            <a class="btn btn-outline-secondary" href="{{ route('teacher.assessments.attempts', $assessment->id) }}">Back</a>
        </div>

        <div class="card card-body mb-3">
            <div><strong>Assessment:</strong> {{ $assessment->title }}</div>
            <div><strong>Student:</strong> {{ $attempt->student->name ?? ('Student #'.$attempt->student_id) }}</div>
            <div><strong>Status:</strong> {{ $attempt->status }}</div>
            <div><strong>Total:</strong> {{ $attempt->total_marks_obtained }} / {{ $assessment->total_marks }}</div>
        </div>

        @foreach($attempt->answers as $ans)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div><strong>Q:</strong> {{ $ans->question->question_text }}</div>
                        <div class="badge bg-primary">{{ $ans->question->marks }} max</div>
                    </div>

                    <hr>

                    <div><strong>Student Answer:</strong></div>
                    <div class="mb-2">
                        @if($ans->selectedOption)
                            {{ $ans->selectedOption->option_text }}
                        @elseif($ans->answer_text)
                            {{ $ans->answer_text }}
                        @elseif($ans->hotspot_x !== null)
                            X={{ $ans->hotspot_x }}, Y={{ $ans->hotspot_y }}
                        @else
                            -
                        @endif
                    </div>

                    <div><strong>Current Marks:</strong> {{ $ans->marks_obtained }}</div>

                    @if(!$ans->is_auto_graded)
                        <form class="mt-2" method="POST" action="{{ route('teacher.assessments.answer.grade', $ans->id) }}">
                            @csrf
                            <div class="row g-2 align-items-center">
                                <div class="col-md-3">
                                    <input type="number" step="0.01" class="form-control" name="marks_obtained" value="{{ $ans->marks_obtained }}">
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-success">Save Grade</button>
                                </div>
                            </div>
                            <small class="text-muted">Manual grading (Essay)</small>
                        </form>
                    @else
                        <small class="text-muted">Auto-graded</small>
                    @endif
                </div>
            </div>
        @endforeach

        <form method="POST" action="{{ route('teacher.assessments.finalize', $attempt->id) }}" onsubmit="return confirm('Finalize grades for this attempt?')">
            @csrf
            <button class="btn btn-dark">Finalize Attempt</button>
        </form>
    </div>
@endsection
