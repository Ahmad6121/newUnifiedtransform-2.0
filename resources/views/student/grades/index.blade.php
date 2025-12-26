@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')

            <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
                <div class="row pt-2">
                    <div class="col ps-4">

                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h1 class="display-6 mb-0">
                                <i class="bi bi-bar-chart-line"></i> My Grades
                            </h1>

                            {{-- ✅ FIX: route student.report.card غير موجود --}}
                            @if(\Illuminate\Support\Facades\Route::has('reportcard.my'))
                                <a href="{{ route('reportcard.my') }}" class="btn btn-outline-primary">
                                    <i class="bi bi-file-earmark-text"></i> Report Card
                                </a>
                            @endif
                        </div>

                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">My Grades</li>
                            </ol>
                        </nav>

                        @include('session-messages')

                        <div class="card shadow-sm">
                            <div class="card-body">

                                {{-- لو الكنترولر ببعت grades/rows/records بأي اسم، بنحاول نعرض الموجود --}}
                                @php
                                    $rows =
                                        $grades
                                        ?? $records
                                        ?? $results
                                        ?? $marks
                                        ?? null;
                                @endphp

                                @if(is_iterable($rows) && count($rows))
                                    <div class="table-responsive">
                                        <table class="table table-striped align-middle">
                                            <thead>
                                            <tr>
                                                <th>Course</th>
                                                <th>Assessment</th>
                                                <th class="text-center">Score</th>
                                                <th class="text-center">Max</th>
                                                <th class="text-end">Date</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($rows as $r)
                                                <tr>
                                                    <td>{{ $r->course_name ?? optional($r->course)->course_name ?? '-' }}</td>
                                                    <td>{{ $r->assessment_title ?? optional($r->assessment)->title ?? '-' }}</td>
                                                    <td class="text-center">{{ $r->score ?? $r->marks ?? '-' }}</td>
                                                    <td class="text-center">{{ $r->max_score ?? $r->total ?? '-' }}</td>
                                                    <td class="text-end">
                                                        {{ optional($r->created_at ?? $r->date ?? null)->format('d M Y') ?? '-' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    {{-- pagination لو موجود --}}
                                    @if(method_exists($rows, 'links'))
                                        <div class="mt-2">
                                            {{ $rows->links() }}
                                        </div>
                                    @endif
                                @else
                                    <p class="text-muted mb-0">No grades available yet.</p>
                                @endif

                            </div>
                        </div>

                    </div>
                </div>

                @include('layouts.footer')
            </div>
        </div>
    </div>
@endsection
