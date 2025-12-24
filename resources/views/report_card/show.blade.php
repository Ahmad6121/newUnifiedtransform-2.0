@extends('layouts.app')

@section('content')
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0">Report Card</h4>
                <div class="text-muted">{{ $student->name }}</div>
            </div>
            <div class="badge bg-dark" style="font-size:16px;">Overall: {{ $overall }}</div>
        </div>

        @if($note)
            <div class="alert alert-warning">{{ $note }}</div>
        @endif

        @forelse($rows as $row)
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between">
                    <strong>{{ $row['course'] }}</strong>
                    <strong>Final: {{ $row['final'] }}</strong>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                        <tr>
                            <th>Assessment</th>
                            <th>Type</th>
                            <th>Mark</th>
                            <th>Total</th>
                            <th>Weight</th>
                            <th>Percent</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($row['items'] as $it)
                            <tr>
                                <td>{{ $it['title'] }}</td>
                                <td>{{ strtoupper($it['kind']) }}</td>
                                <td>{{ $it['mark'] }}</td>
                                <td>{{ $it['total'] }}</td>
                                <td>{{ $it['weight'] }}%</td>
                                <td>{{ $it['percent'] }}%</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="alert alert-info">No published results yet.</div>
        @endforelse

    </div>
@endsection
