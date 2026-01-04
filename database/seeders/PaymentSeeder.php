<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ جيب أول فاتورة unpaid أو partial
        $invoice = Invoice::whereIn('status', ['unpaid', 'partial'])->first();
        if (!$invoice) {
            return;
        }

        // ✅ مين يستلم الدفع؟ (يفضل Accountant ثم Admin)
        $receiver = null;

        if (Schema::hasColumn('users', 'role')) {
            $receiver = User::where('role', 'accountant')->first()
                ?: User::where('role', 'admin')->first();
        }

        if (!$receiver) {
            $receiver = User::all()->first(function ($u) {
                return (method_exists($u, 'hasRole') && $u->hasRole('accountant'));
            }) ?: User::all()->first(function ($u) {
                return (method_exists($u, 'hasRole') && $u->hasRole('admin'));
            });
        }

        if (!$receiver) {
            $receiver = User::first();
        }

        // ✅ مبلغ الدفع (مثال: نصف الفاتورة)
        $payAmount = (float) ($invoice->amount ?? 0);
        if ($payAmount <= 0) {
            return;
        }
        $payAmount = round($payAmount / 2, 2);

        // ✅ حضّر بيانات الدفع حسب أعمدة جدول payments الموجودة
        $paymentData = [
            'invoice_id' => $invoice->id,
            'amount'     => $payAmount,
        ];

        // payment_method (الكود الجديد عندك يستخدمها)
        if (Schema::hasColumn('payments', 'payment_method')) {
            $paymentData['payment_method'] = 'Cash';
        }

        // date (الكود الجديد عندك يستخدمها)
        if (Schema::hasColumn('payments', 'date')) {
            $paymentData['date'] = Carbon::now();
        }

        // دعم قديم لو كان عندك أعمدة قديمة لسه موجودة
        if (Schema::hasColumn('payments', 'method')) {
            $paymentData['method'] = 'cash';
        }
        if (Schema::hasColumn('payments', 'paid_at')) {
            $paymentData['paid_at'] = Carbon::now();
        }
        if (Schema::hasColumn('payments', 'received_by')) {
            $paymentData['received_by'] = $receiver ? $receiver->id : null;
        }
        if (Schema::hasColumn('payments', 'reference')) {
            $paymentData['reference'] = 'PMT-TEST-' . rand(100, 999);
        }
        if (Schema::hasColumn('payments', 'notes')) {
            $paymentData['notes'] = 'Seeded test payment';
        }

        Payment::create($paymentData);

        // ✅ تحديث الفاتورة حسب منطق النظام الجديد
        if (Schema::hasColumn('invoices', 'paid_amount')) {
            $invoice->paid_amount = (float) $invoice->paid_amount + $payAmount;
        }

        // status
        $paidAmount = Schema::hasColumn('invoices', 'paid_amount') ? (float) $invoice->paid_amount : 0;

        if ($paidAmount >= (float) $invoice->amount) {
            $invoice->status = 'paid';
        } else {
            $invoice->status = 'partial';
        }

        $invoice->save();
    }
}
