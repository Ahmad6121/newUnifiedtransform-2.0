@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')

            <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
                <div class="row pt-2">
                    <div class="col ps-4">

                        <h1 class="display-6 mb-3">
                            <i class="bi bi-calendar2-week"></i> Take Attendance
                        </h1>

                        @include('session-messages')

                        {{-- ✅ اختيار الصف والشعبة --}}
                        <div class="card mb-3 border-0 shadow-sm">
                            <div class="card-body">
                                <form method="GET" action="{{ url('/attendances/take') }}" class="row g-2 align-items-end">
                                    <div class="col-md-5">
                                        <label class="form-label fw-bold">Class</label>
                                        <select name="class_id" class="form-select"
                                                onchange="this.form.submit()">
                                            <option value="0">-- Select Class --</option>
                                            @foreach(($classes ?? collect()) as $c)
                                                <option value="{{ $c->id }}" {{ (int)($classId ?? 0) === (int)$c->id ? 'selected' : '' }}>
                                                    {{ $c->class_name ?? ('Class #'.$c->id) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-5">
                                        <label class="form-label fw-bold">Section</label>
                                        <select name="section_id" class="form-select" {{ (int)($classId ?? 0) > 0 ? '' : 'disabled' }}>
                                            <option value="0">-- Select Section --</option>
                                            @foreach(($sections ?? collect()) as $s)
                                                <option value="{{ $s->id }}" {{ (int)($sectionId ?? 0) === (int)$s->id ? 'selected' : '' }}>
                                                    {{ $s->section_name ?? ('Section #'.$s->id) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2 d-grid">
                                        <button class="btn btn-dark" {{ (int)($classId ?? 0) > 0 ? '' : 'disabled' }}>
                                            Load Students
                                        </button>
                                    </div>
                                </form>

                                @if((int)($classId ?? 0) === 0)
                                    <div class="text-muted mt-2">Select a class first (only your assigned classes will appear).</div>
                                @elseif((int)($classId ?? 0) > 0 && (($sections ?? collect())->count() == 0))
                                    <div class="alert alert-warning mt-2 mb-0">
                                        No sections assigned to you for this class.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <h5 class="mb-2">
                            <i class="bi bi-compass"></i>
                            Class: <b>{{ $className ?? '' }}</b>
                            @if(!empty($sectionName))
                                , Section: <b>{{ $sectionName }}</b>
                            @endif
                        </h5>

                        <div class="text-muted mb-3">Current Date and Time: {{ date('Y-m-d H:i:s') }}</div>

                        @if((int)($classId ?? 0) > 0 && (int)($sectionId ?? 0) > 0)

                            @if(($attendance_count ?? 0) > 0)
                                <div class="alert alert-info">
                                    Attendance already taken for today ✅ (You can’t submit again).
                                </div>
                            @endif

                            <div class="row mt-2">
                                <div class="col-12 bg-white border p-3 shadow-sm">

                                    <form action="{{ route('attendances.store') }}" method="POST">
                                        @csrf

                                        <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">
                                        <input type="hidden" name="class_id" value="{{ $classId }}">
                                        <input type="hidden" name="section_id" value="{{ $sectionId }}">
                                        <input type="hidden" name="course_id" value="0">

                                        <table class="table align-middle">
                                            <thead>
                                            <tr>
                                                <th style="width:180px"># ID Card Number</th>
                                                <th>Student Name</th>
                                                <th style="width:260px">Status</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse(($student_list ?? collect()) as $st)
                                                <input type="hidden" name="student_ids[]" value="{{ $st->student_id }}">

                                                <tr>
                                                    <td>{{ $st->id_card_number ?? '-' }}</td>
                                                    <td>{{ $st->first_name }} {{ $st->last_name }}</td>
                                                    <td>
                                                        <div class="d-flex gap-3">
                                                            <label class="d-flex align-items-center gap-2">
                                                                <input type="radio"
                                                                       class="form-check-input"
                                                                       name="status[{{ $st->student_id }}]"
                                                                       value="on"
                                                                       checked>
                                                                <span class="badge bg-success">PRESENT</span>
                                                            </label>

                                                            <label class="d-flex align-items-center gap-2">
                                                                <input type="radio"
                                                                       class="form-check-input"
                                                                       name="status[{{ $st->student_id }}]"
                                                                       value="off">
                                                                <span class="badge bg-danger">ABSENT</span>
                                                            </label>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-4">
                                                        No students found for this Class/Section.
                                                    </td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                        </table>

                                        @if(($student_list ?? collect())->count() > 0 && ($attendance_count ?? 0) < 1)
                                            <div class="mt-3">
                                                <button type="submit" class="btn btn-outline-primary">
                                                    <i class="bi bi-check2"></i> Submit Attendance
                                                </button>
                                            </div>
                                        @endif
                                    </form>

                                </div>
                            </div>

                        @endif

                    </div>
                </div>

                @include('layouts.footer')
            </div>
        </div>
    </div>
@endsection
