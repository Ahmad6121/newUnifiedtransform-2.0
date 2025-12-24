@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0">Report Card</h4>
                <div class="text-muted">
                    Student: {{ $student->name ?? '-' }}
                    @if($promotion)
                        | Class: {{ $promotion->class_id }} | Section: {{ $promotion->section_id }}
                    @endif
                </div>
            </div>
            <a class="btn btn-dark" href="{{ route('student.report.card.pdf') }}">Download PDF</a>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card card-body">
                    <div class="text-muted">Overall Average</div>
                    <h3 class="mb-0">{{ $avg }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-body">
                    <div class="text-muted">Rank (Class/Section)</div>
                    <h3 class="mb-0">{{ $rank ?? '-' }} @if($total_students) <small class="text-muted">/ {{ $total_students }}</small> @endif</h3>
                </div>
            </div>
        </div>

        @foreach($details as $d)
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between">
                    <strong>{{ $d['course']->course_name }}</strong>
                    <span>Final: <strong>{{ $d['final'] }}</strong></span>
                </div>
                <div class="card-body">
                    @if(count($d['assessments']) === 0)
                        <div class="text-muted">No published results yet.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                <tr>
                                    <th>Assessment</th>
                                    <th>Kind</th>
                                    <th>Weight%</th>
                                    <th>Mark</th>
                                    <th>Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($d['assessments'] as $it)
                                    <tr>
                                        <td>{{ $it['assessment']->title }}</td>
                                        <td>{{ strtoupper($it['assessment']->kind) }}</td>
                                        <td>{{ $it['assessment']->weight_percent }}</td>
                                        <td>{{ $it['mark'] }}</td>
                                        <td>{{ $it['assessment']->total_marks }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endsection
