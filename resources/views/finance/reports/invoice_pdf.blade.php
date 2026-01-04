<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>School Invoice</title>
    <style>
        body { font-family: sans-serif; line-height: 1.4; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #333; margin-bottom: 20px; }
        .school-name { font-size: 24px; font-weight: bold; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .main-table { width: 100%; border-collapse: collapse; }
        .main-table th, .main-table td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        .main-table th { background-color: #f8f9fa; }
        .total-row { font-weight: bold; background-color: #f2f2f2; }
        .footer { text-align: center; margin-top: 40px; font-size: 11px; color: #777; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
<div class="header">
    <div class="school-name">SmartSchool</div>
    <h2>OFFICIAL TUITION INVOICE</h2>
</div>

<table class="info-table">
    <tr>
        <td><strong>Student Name:</strong> {{ $invoice->student->first_name }} {{ $invoice->student->last_name }}</td>
        <td style="text-align: right;"><strong>Invoice ID:</strong> #{{ $invoice->id }}</td>
    </tr>
    <tr>
        <td><strong>Date:</strong> {{ $date }}</td>
        <td style="text-align: right;"><strong>Status:</strong> {{ strtoupper($invoice->status) }}</td>
    </tr>
</table>

<table class="main-table">
    <thead>
    <tr>
        <th>Description</th>
        <th>Total Amount</th>
        <th>Paid Amount</th>
        <th>Remaining Balance</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>Academic Fees & Services</td>
        <td>${{ number_format($invoice->amount, 2) }}</td>
        <td>${{ number_format($invoice->paid_amount, 2) }}</td>
        <td style="color: red;">${{ number_format($invoice->amount - $invoice->paid_amount, 2) }}</td>
    </tr>
    </tbody>
</table>

<div class="footer">
    This is a computer-generated document. No signature required.
    <br>Thank you for choosing Unified Transform.
</div>
</body>
</html>
