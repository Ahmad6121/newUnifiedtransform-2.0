<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Assessment Results</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { margin: 0 0 8px; }
        .meta { margin: 0 0 12px; color: #444; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #222; padding: 6px; }
        th { background: #f2f2f2; }
        .right { text-align: right; }
        .center { text-align: center; }
    </style>
</head>
<body>

<h2>Assessment Results</h2>

<div class="meta">
    <div><b>Title:</b> {{ $assessment->title }}</div>
    <div><b>Course:</b> {{ $courseName }} &nbsp;|&nbsp; <b>Class:</b> {{ $className }}</div>
    <div><b>Total Marks:</b> {{ number_format((float)($assessment->total_marks ?? 100), 2) }}</div>
</div>

<table>
    <thead>
    <tr>
        <th class="center" style="width:60px;">ID</th>
        <th>Student</th>
        <th class="right" style="width:90px;">Marks</th>
        <th class="center" style="width:80px;">Percent</th>
        <th class="center" style="width:70px;">Status</th>
    </tr>
    </thead>
    <tbody>
    @foreach($students as $s)
        @php
            $res = $resultsMap[(int)$s->id] ?? null;
            $marks = $res->marks_obtained ?? null;
            $total = (float)($assessment->total_marks ?? 100);
            $percent = ($marks !== null && $total > 0) ? round(((float)$marks / $total) * 100, 2) : null;
            $status = ($percent === null) ? '-' : (($percent >= 50) ? 'PASS' : 'FAIL');
        @endphp
        <tr>
            <td class="center">{{ $s->id }}</td>
            <td>{{ $displayUserName($s) }}</td>
            <td class="right">{{ $marks === null ? '-' : number_format((float)$marks, 2) }}</td>
            <td class="center">{{ $percent === null ? '-' : (rtrim(rtrim(number_format($percent,2), '0'), '.') . '%') }}</td>
            <td class="center">{{ $status }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
