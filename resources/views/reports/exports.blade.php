@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-3">Reports Export</h3>

        @php
            $sessionIdVal = $sessionId ?? '';
        @endphp

        <div class="card mb-4">
            <div class="card-header"><b>Class Gradebook (CSV / PDF)</b></div>
            <div class="card-body">

                <form method="GET" action="{{ route('reports.class.gradebook.csv') }}" class="row g-2 mb-2">
                    <input type="hidden" name="session_id" value="{{ $sessionIdVal }}">

                    <div class="col-md-3">
                        <label class="form-label">Class</label>
                        <select name="class_id" class="form-select" required>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->class_name ?? ('Class #' . $c->id) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Section (optional)</label>
                        <select name="section_id" class="form-select">
                            <option value="">-- All --</option>
                            @foreach($sections as $s)
                                <option value="{{ $s->id }}">{{ $s->section_name ?? ('Section #' . $s->id) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Course</label>
                        <select name="course_id" class="form-select" required>
                            @foreach($courses as $co)
                                <option value="{{ $co->id }}">{{ $co->course_name ?? ('Course #' . $co->id) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-success w-100" type="submit">Download CSV</button>
                    </div>
                </form>

                <form method="GET" action="{{ route('reports.class.gradebook.pdf') }}" class="row g-2">
                    <input type="hidden" name="session_id" value="{{ $sessionIdVal }}">

                    <div class="col-md-3">
                        <select name="class_id" class="form-select" required>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->class_name ?? ('Class #' . $c->id) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select name="section_id" class="form-select">
                            <option value="">-- All --</option>
                            @foreach($sections as $s)
                                <option value="{{ $s->id }}">{{ $s->section_name ?? ('Section #' . $s->id) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <select name="course_id" class="form-select" required>
                            @foreach($courses as $co)
                                <option value="{{ $co->id }}">{{ $co->course_name ?? ('Course #' . $co->id) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-danger w-100" type="submit">Download PDF</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection
