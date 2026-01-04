<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>وصل دفع رقم #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: sans-serif; padding: 40px; color: #333; line-height: 1.6; text-align: right; }
        .invoice-box { max-width: 800px; margin: auto; border: 1px solid #eee; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; align-items: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #eee; padding: 15px; text-align: right; }
        th { background: #f9f9f9; }
        .status { font-weight: bold; padding: 5px 10px; border-radius: 5px; }
        .paid { color: green; } .unpaid { color: red; }
        @media print { .no-print { display: none; } }
        .print-btn { background: #28a745; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 5px; margin-bottom: 20px; font-size: 16px; }
    </style>
</head>
<body>
<div class="no-print" style="text-align: center;">
    <button onclick="window.print()" class="print-btn">إضغط هنا للطباعة أو الحفظ كـ PDF 🖨️</button>
</div>

<div class="invoice-box">
    <div class="header">
        <div style="text-align: right;">
            <h1 style="margin:0;">وصل دفع رسمي</h1>
            <p>نظام الإدارة المدرسية الحديث</p>
        </div>
        <div style="text-align: left;">
            <p>التاريخ: {{ date('Y-m-d') }}</p>
            <p>رقم الفاتورة: <strong>#{{ $invoice->invoice_number }}</strong></p>
        </div>
    </div>

    <div style="margin-bottom: 30px;">
        <p><strong>اسم الطالب:</strong> {{ $invoice->student->first_name }} {{ $invoice->student->last_name }}</p>
        <p><strong>الصف الدراسي:</strong> {{ $invoice->class->class_name }}</p>
        <p><strong>بيان الفاتورة:</strong> {{ $invoice->title }}</p>
    </div>

    <table>
        <thead>
        <tr>
            <th>المبلغ الإجمالي</th>
            <th>المبلغ المدفوع</th>
            <th>المتبقي (Balance)</th>
            <th>حالة الدفع</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>${{ number_format($invoice->amount, 2) }}</td>
            <td>${{ number_format($invoice->paid_amount, 2) }}</td>
            <td style="color: red; font-weight: bold;">${{ number_format($invoice->balance, 2) }}</td>
            <td>
                        <span class="status {{ $invoice->status == 'paid' ? 'paid' : 'unpaid' }}">
                            {{ strtoupper($invoice->status) }}
                        </span>
            </td>
        </tr>
        </tbody>
    </table>

    <div style="margin-top: 50px; text-align: center; font-size: 12px; color: #888; border-top: 1px solid #eee; padding-top: 10px;">
        <p>هذا الوصل مستخرج إلكترونياً من نظام المحاسبة ولا يحتاج إلى ختم أو توقيع.</p>
    </div>
</div>
</body>
</html>
