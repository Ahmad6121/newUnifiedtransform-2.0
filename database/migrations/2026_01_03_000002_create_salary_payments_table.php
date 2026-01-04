<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalaryPaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');       // الموظف
            $table->decimal('amount', 10, 2);
            $table->date('salary_month');               // خزن أول يوم بالشهر (مثلا 2026-01-01)
            $table->dateTime('paid_at')->nullable();
            $table->string('payment_method', 50)->nullable(); // Cash/Bank...
            $table->string('reference', 100)->nullable();
            $table->unsignedBigInteger('paid_by')->nullable(); // admin/accountant
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'salary_month']);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('paid_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('salary_payments');
    }
}
