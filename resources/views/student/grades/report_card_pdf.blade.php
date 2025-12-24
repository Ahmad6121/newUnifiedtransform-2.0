<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report Card</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
        .h { font-size: 18px; font-weight: bold; margin-bottom: 6px; }
        .muted { color: #666; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background: #f2f2f2; }
        .box { border: 1px solid #ddd; padding: 10px; margin-bottom: 12px; }
    </style>
</head>
<body>
<div class="h">Report Card</div>
<div class="muted">
    Student: {{ $student->name ?? '-' }}
    @if($promotion) | Class: {{ $promotion->class_id }} | Section: {{ $promotion->section_id }} @endif
</div>

<div class="box">
    <strong>Overall Average:</strong> {{ $avg }} <br>
    <strong>Rank:</strong> {{ $rank ?? '-' }} @if($total_students) / {{ $total_students }} @endif
</div>

@foreach($details as $d)
    <div class="box">
        <div><strong>{{ $d['course']->course_name }}</strong> — Final: <strong>{{ $d['final'] }}</strong></div>
    </div>

    <table>
        <thead>
        <tr>
            <th>Assessment</th><th>Kind</th><th>Weight%</th><th>Mark</th><th>Total</th>
        </tr>
        </thead>
        <tbody>
        @forelse($d['assessments'] as $it)
            <tr>
                <td>{{ $it['assessment']->title }}</td>
                <td>{{ strtoupper($it['assessment']->kind) }}</td>
                <td>{{ $it['assessment']->weight_percent }}</td>
                <td>{{ $it['mark'] }}</td>
                <td>{{ $it['assessment']->total_marks }}</td>
            </tr>
        @empty
            <tr><td colspan="5">No published results yet.</td></tr>
        @endforelse
        </tbody>
    </table>
@endforeach

<script>
    // fallback للطباعة لو DOMPDF مش مركب
    // window.print();
</script>
</body>
</html>
