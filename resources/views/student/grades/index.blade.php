@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')

            <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
                <div class="row pt-2">
                    <div class="col ps-4">

                        <h1 class="display-6 mb-3">
                            <i class="bi bi-bar-chart-line"></i> My Grades
                        </h1>

                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">My Grades</li>
                            </ol>
                        </nav>

                        @if(!empty($note))
                            <div class="alert alert-warning">{{ $note }}</div>
                        @endif

                        <div class="card shadow-sm">
                            <div class="card-body p-0">
                                <table class="table table-striped mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Course</th>
                                        <th>Assessment</th>
                                        <th>Type</th>
                                        <th class="text-end">Mark</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-end">Weight %</th>
                                        <th class="text-end">Percent</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse(($rows ?? []) as $r)
                                        <tr>
                                            <td>{{ $r['course'] }}</td>
                                            <td>{{ $r['assessment'] }}</td>
                                            <td>{{ $r['type'] }}</td>
                                            <td class="text-end">{{ number_format($r['mark'], 2) }}</td>
                                            <td class="text-end">{{ number_format($r['total'], 2) }}</td>
                                            <td class="text-end">{{ number_format($r['weight'], 2) }}</td>
                                            <td class="text-end">{{ number_format($r['percent'], 2) }}%</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                No grades available yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-3">
                            <a class="btn btn-outline-dark" href="{{ route('reportcard.my') }}">
                                <i class="bi bi-file-earmark-text"></i> View Full Report Card
                            </a>
                        </div>

                    </div>
                </div>

                @include('layouts.footer')
            </div>
        </div>
    </div>
@endsection
