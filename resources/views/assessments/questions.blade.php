@extends('layouts.app')

@section('content')
    <div class="container">
        @if(session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Builder: {{ $assessment->title }}</h4>
            <a class="btn btn-outline-secondary" href="{{ route('assessments.index') }}">Back</a>
        </div>

        <div class="card card-body mb-4">
            <form method="POST" action="{{ route('assessments.questions.store', $assessment->id) }}" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Question Type</label>
                        <select name="question_type" class="form-select" required>
                            <option value="mcq">MCQ</option>
                            <option value="true_false">True/False</option>
                            <option value="essay">Essay</option>
                            <option value="fill_blank">Fill in the Blank</option>
                            <option value="hotspot">Hotspot (on image)</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Marks</label>
                        <input type="number" step="0.01" name="marks" class="form-control" value="1" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Order</label>
                        <input type="number" name="order" class="form-control" placeholder="auto">
                    </div>

                    <div class="col-md-5">
                        <label class="form-label">Image (optional)</label>
                        <input type="file" name="image" class="form-control">
                        <small class="text-muted">For Hotspot questions upload an image.</small>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Question Text</label>
                        <textarea name="question_text" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="col-12">
                        <hr>
                        <h6>MCQ / True-False Options</h6>
                        <div class="row g-2">
                            @for($i=0;$i<4;$i++)
                                <div class="col-md-5">
                                    <input class="form-control" name="options[{{ $i }}]" placeholder="Option {{ $i+1 }}">
                                </div>
                                <div class="col-md-1 d-flex align-items-center">
                                    <input type="radio" name="correct_index" value="{{ $i }}" class="form-check-input">
                                    <small class="ms-1">Correct</small>
                                </div>
                            @endfor
                        </div>
                        <small class="text-muted">For True/False: you can leave options empty, system will use (True/False) and correct = True.</small>

                        <hr>
                        <h6>Fill in the Blank</h6>
                        <input class="form-control" name="correct_text" placeholder="Correct answer text">

                        <hr>
                        <h6>Hotspot Settings</h6>
                        <div class="row g-2">
                            <div class="col-md-4"><input class="form-control" name="hotspot_x" placeholder="Correct X (px)"></div>
                            <div class="col-md-4"><input class="form-control" name="hotspot_y" placeholder="Correct Y (px)"></div>
                            <div class="col-md-4"><input class="form-control" name="hotspot_radius" placeholder="Radius (px) e.g. 30"></div>
                        </div>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary">Add Question</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                    <tr>
                        <th>Order</th>
                        <th>Type</th>
                        <th>Marks</th>
                        <th>Question</th>
                        <th>Options / Settings</th>
                        <th style="width:120px;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($assessment->questions as $q)
                        <tr>
                            <td>{{ $q->order }}</td>
                            <td>{{ strtoupper($q->question_type) }}</td>
                            <td>{{ $q->marks }}</td>
                            <td>
                                <div>{{ $q->question_text }}</div>
                                @if($q->image_path)
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/'.$q->image_path) }}" style="max-width:200px;border:1px solid #ddd;">
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if(in_array($q->question_type,['mcq','true_false']))
                                    <ul class="mb-0">
                                        @foreach($q->options as $o)
                                            <li>{{ $o->option_text }} @if($o->is_correct) <strong>(Correct)</strong> @endif</li>
                                        @endforeach
                                    </ul>
                                @elseif($q->question_type==='fill_blank')
                                    <div>Correct: <strong>{{ $q->correct_text }}</strong></div>
                                @elseif($q->question_type==='hotspot')
                                    <div>X={{ $q->hotspot_x }}, Y={{ $q->hotspot_y }}, R={{ $q->hotspot_radius }}</div>
                                @else
                                    <div>Manual grading</div>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('assessments.questions.delete', $q->id) }}" onsubmit="return confirm('Delete this question?')">
                                    @csrf
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4">No questions yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
