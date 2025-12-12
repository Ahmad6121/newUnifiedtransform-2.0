@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
                <h3 class="mb-1">📅 جدول الحصص</h3>
                <div class="text-muted small">
                    @if(isset($class)) الصف: <strong>{{ $class->class_name }}</strong>@endif
                    @if(isset($section)) &nbsp;|&nbsp; الشعبة: <strong>{{ $section->section_name }}</strong>@endif
                </div>
            </div>
            <div>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">رجوع</a>
            </div>
        </div>

        @if($routines->isEmpty())
            <div class="alert alert-info">لا توجد حصص لهذا الصف/الشعبة.</div>
        @else
            @php
                // ترتيب الأيام وضبط التجميع
                $dayOrder = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
                $byDay = $routines->groupBy('day')->sortBy(function($_, $day) use ($dayOrder) {
                    $idx = array_search($day, $dayOrder);
                    return $idx === false ? 999 : $idx;
                });
            @endphp

            @foreach($byDay as $day => $items)
                <div class="card mb-4 shadow-sm">
                    <div class="card-header fw-bold">
                        {{ __($day) }} {{-- اطبع اليوم كما هو، أو اعمل ترجمة لو بدّك --}}
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th style="width:22%">المادة</th>
                                    <th style="width:22%">المعلّم</th>
                                    <th style="width:14%">وقت البداية</th>
                                    <th style="width:14%">وقت النهاية</th>
                                    <th style="width:14%">الغرفة</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($items->sortBy('start_time') as $row)
                                    <tr>
                                        <td>{{ optional($row->course)->course_name ?? '-' }}</td>
                                        <td>
                                            @php
                                                $t = $row->teacher;
                                                $tName = $t ? trim(($t->first_name ?? '').' '.($t->last_name ?? '')) : '-';
                                            @endphp
                                            {{ $tName !== '' ? $tName : '-' }}
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($row->start_time)->format('H:i') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($row->end_time)->format('H:i') }}</td>
                                        <td>{{ $row->room_no ?? '-' }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div> {{-- /table-responsive --}}
                    </div> {{-- /card-body --}}
                </div>
            @endforeach
        @endif
    </div>
@endsection
