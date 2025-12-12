<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\User;
use Carbon\Carbon;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $invoice = Invoice::where('status', 'unpaid')->first();
        $admin = User::where('role', 'admin')->first();

        if ($invoice && $admin) {
            Payment::firstOrCreate([
                'invoice_id' => $invoice->id,
                'amount' => $invoice->amount,
            ], [
                'method' => 'cash',
                'reference' => 'PMT-TEST-001',
                'paid_at' => Carbon::now(),
                'received_by' => $admin->id,
                'notes' => 'Seeded test payment',
            ]);

            // نحدث حالة الفاتورة لتصبح "paid"
            $invoice->update(['status' => 'paid']);
        }
    }
}
