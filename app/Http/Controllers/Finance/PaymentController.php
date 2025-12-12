<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'amount'=>'required|numeric|min:0.01',
            'method'=>'required|in:cash,card,transfer,online',
            'reference'=>'nullable|string|max:255',
            'notes'=>'nullable|string|max:2000'
        ]);

        $data['invoice_id'] = $invoice->id;
        $data['paid_at'] = now();
        $data['received_by'] = auth()->id();

        Payment::create($data);

        // تحديث حالة الفاتورة
        $paidTotal = $invoice->paidTotal() + $data['amount'];
        if ($paidTotal >= $invoice->amount) {
            $invoice->update(['status' => 'paid']);
        } elseif ($paidTotal > 0) {
            $invoice->update(['status' => 'partial']);
        }

        return back()->with('status','Payment added');
    }
}
