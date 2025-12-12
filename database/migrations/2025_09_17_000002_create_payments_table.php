<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id'); // invoices.id
            $table->decimal('amount', 10, 2);
            $table->enum('method', ['cash','card','transfer','online'])->default('cash');
            $table->string('reference')->nullable();  // رقم إيصال/حوالة
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('received_by')->nullable(); // users.id (موظف/أدمِن)
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
            $table->foreign('received_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['invoice_id','paid_at']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('payments');
    }
};
