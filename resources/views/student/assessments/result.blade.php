@extends('layouts.app')

@section('content')
    <div class="container">
        @if(session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif

        <h4 class="mb-2">Result: {{ $assessment->title }}</h4>

        @if(!$canSee)
            <div class="alert alert-warning">
                Result is not published yet. Please check later.
            </div>
            @return
        @endif

        <div class="card card-body mb-3">
            <div><strong>Status:</strong> {{ $attempt->status }}</div>
            <div><strong>Total:</strong> {{ $attempt->total_marks_obtained }} / {{ $assessment->total_marks }}</div>
            <div><strong>Auto:</strong> {{ $attempt->auto_marks }} | <strong>Manual:</strong> {{ $attempt->manual_marks }}</div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                    <tr>
                        <th>Question</th>
                        <th>Answer</th>
                        <th>Marks</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($attempt->answers as $ans)
                        <tr>
                            <td>{{ $ans->question->question_text }}</td>
                            <td>
                                @if($ans->selectedOption)
                                    {{ $ans->selectedOption->option_text }}
                                @elseif($ans->answer_text)
                                    {{ $ans->answer_text }}
                                @elseif($ans->hotspot_x !== null)
                                    X={{ $ans->hotspot_x }}, Y={{ $ans->hotspot_y }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $ans->marks_obtained }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
