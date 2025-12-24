@extends('layouts.app')

@section('content')
    <div class="container">
        <h4 class="mb-2">{{ $assessment->title }}</h4>
        <div class="text-muted mb-3">
            Attempt #{{ $attempt->id }} |
            Duration: {{ $assessment->duration_minutes ? $assessment->duration_minutes.' min' : 'No limit' }}
        </div>

        <form method="POST" action="{{ route('student.assessments.submit', $attempt->id) }}">
            @csrf

            @foreach($questions as $index => $q)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-2">Q{{ $index+1 }} ({{ strtoupper($q->question_type) }})</h6>
                            <span class="badge bg-primary">{{ $q->marks }} marks</span>
                        </div>

                        <div class="mb-2">{{ $q->question_text }}</div>

                        @if($q->image_path)
                            <div class="mb-3">
                                <img id="img-{{ $q->id }}" src="{{ asset('storage/'.$q->image_path) }}"
                                     style="max-width:100%;border:1px solid #ddd;cursor:crosshair;">
                                <small class="text-muted d-block mt-1">
                                    Hotspot: click on the correct point.
                                </small>
                            </div>
                        @endif

                        @if(in_array($q->question_type,['mcq','true_false']))
                            @foreach($q->options as $o)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="mcq[{{ $q->id }}]" value="{{ $o->id }}" id="opt-{{ $o->id }}">
                                    <label class="form-check-label" for="opt-{{ $o->id }}">{{ $o->option_text }}</label>
                                </div>
                            @endforeach
                        @elseif($q->question_type==='fill_blank')
                            <input class="form-control" name="fill[{{ $q->id }}]" placeholder="Type your answer">
                        @elseif($q->question_type==='essay')
                            <textarea class="form-control" name="essay[{{ $q->id }}]" rows="4" placeholder="Write your answer"></textarea>
                        @elseif($q->question_type==='hotspot')
                            <input type="hidden" name="hotspot_x[{{ $q->id }}]" id="hx-{{ $q->id }}">
                            <input type="hidden" name="hotspot_y[{{ $q->id }}]" id="hy-{{ $q->id }}">
                            <div class="text-muted">Click on the image to set your answer point.</div>
                        @endif
                    </div>
                </div>
            @endforeach

            <button class="btn btn-success" onclick="return confirm('Submit now?')">Submit</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @foreach($questions as $q)
            @if($q->question_type==='hotspot' && $q->image_path)
            const img{{ $q->id }} = document.getElementById('img-{{ $q->id }}');
            if (img{{ $q->id }}) {
                img{{ $q->id }}.addEventListener('click', function (e) {
                    const rect = img{{ $q->id }}.getBoundingClientRect();
                    const x = Math.round(e.clientX - rect.left);
                    const y = Math.round(e.clientY - rect.top);
                    document.getElementById('hx-{{ $q->id }}').value = x;
                    document.getElementById('hy-{{ $q->id }}').value = y;
                    alert('Point saved: X=' + x + ', Y=' + y);
                });
            }
            @endif
            @endforeach
        });
    </script>
@endsection
