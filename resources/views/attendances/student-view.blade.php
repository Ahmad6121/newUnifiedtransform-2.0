@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/fullcalendar5.9.0.min.css') }}">
    <script src="{{ asset('js/fullcalendar5.9.0.main.min.js') }}"></script>

    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')

            <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
                <div class="row pt-2">
                    <div class="col ps-4">
                        <h1 class="display-6 mb-3">
                            <i class="bi bi-calendar2-week"></i> Student Attendance
                        </h1>

                        <h5 class="mb-3">
                            <i class="bi bi-person"></i>
                            Student Name: {{ $student->first_name ?? '' }} {{ $student->last_name ?? '' }}
                        </h5>

                        <div class="row mt-3">
                            <div class="col bg-white p-3 border shadow-sm">
                                <div id="attendanceCalendar"></div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col bg-white border shadow-sm p-3">
                                <table class="table table-sm align-middle">
                                    <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Context</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse(($attendances ?? collect()) as $attendance)
                                        <tr>
                                            <td>
                                                @if (($attendance->status ?? '') === 'on')
                                                    <span class="badge bg-success">PRESENT</span>
                                                @else
                                                    <span class="badge bg-danger">ABSENT</span>
                                                @endif
                                            </td>
                                            <td>{{ $attendance->created_at ?? '' }}</td>
                                            <td>{{ $attendance->context ?? '' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">
                                                No attendance records found.
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>

                @include('layouts.footer')
            </div>
        </div>
    </div>

    @php
        $events = [];
        foreach(($attendances ?? []) as $a){
            $events[] = [
                'title' => (($a->status ?? '') === 'on') ? 'Present' : 'Absent',
                'start' => $a->created_at ?? now()->toDateString(),
                'color' => (($a->status ?? '') === 'on') ? 'green' : 'red',
            ];
        }
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('attendanceCalendar');
            var attEvents = @json($events);

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 350,
                events: attEvents,
            });
            calendar.render();
        });
    </script>
@endsection
