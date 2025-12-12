<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');        // users.id (role=student)
            $table->unsignedBigInteger('class_id')->nullable();   // school_classes.id
            $table->unsignedBigInteger('session_id');        // school_sessions.id

            $table->string('title')->default('Tuition Fee');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['unpaid','partial','paid','overdue'])->default('unpaid');
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('class_id')->references('id')->on('school_classes')->nullOnDelete();
            $table->foreign('session_id')->references('id')->on('school_sessions')->cascadeOnDelete();
            $table->index(['student_id','session_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('invoices');
    }
};
