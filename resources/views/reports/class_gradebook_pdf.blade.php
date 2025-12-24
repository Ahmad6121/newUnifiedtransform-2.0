<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Class Gradebook</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h2 { margin: 0 0 8px; }
        .meta { margin: 0 0 12px; color: #444; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #222; padding: 5px; }
        th { background: #f2f2f2; }
        .right { text-align: right; }
        .center { text-align: center; }
    </style>
</head>
<body>

<h2>Class Gradebook</h2>
<div class="meta">
    <div><b>Class:</b> {{ $className }} &nbsp;|&nbsp; <b>Course:</b> {{ $courseName }}</div>
    <div><b>Session ID:</b> {{ $sessionId }} @if($sectionId) | <b>Section:</b> {{ $sectionId }} @endif</div>
</div>

<table>
    <thead>
    <tr>
        <th style="width:60px;" class="center">ID</th>
        <th>Student</th>
        @foreach($assessments as $a)
            <th class="center">{{ $a->title }} ({{ $a->weight_percent }}%)</th>
        @endforeach
        <th class="center" style="width:90px;">Final %</th>
    </tr>
    </thead>
    <tbody>
    @foreach($students as $s)
        @php $final = 0; @endphp
        <tr>
            <td class="center">{{ $s->id }}</td>
            <td>{{ $displayUserName($s) }}</td>

            @foreach($assessments as $a)
                @php
                    $res = \Illuminate\Support\Facades\Schema::hasTable('assessment_results')
                        ? \Illuminate\Support\Facades\DB::table('assessment_results')
                            ->where('assessment_id', $a->id)
                            ->where('student_id', $s->id)
                            ->first()
                        : null;

                    $marks = $res->marks_obtained ?? null;
                    $total = (float)($a->total_marks ?? 100);
                    $weight = (float)($a->weight_percent ?? 0);

                    if ($marks !== null && $total > 0 && $weight > 0) {
                        $final += (((float)$marks / $total) * 100) * ($weight / 100);
                    }
                @endphp
                <td class="center">{{ $marks === null ? '-' : $marks }}</td>
            @endforeach

            <td class="center">{{ round($final, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
