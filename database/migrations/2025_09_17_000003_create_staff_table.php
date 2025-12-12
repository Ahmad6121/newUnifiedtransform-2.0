<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->string('job_title')->default('Staff');
            $table->enum('salary_type',['fixed','hourly'])->default('fixed');
            $table->decimal('base_salary',10,2)->default(0);
            $table->date('join_date')->nullable();
            $table->enum('status',['active','inactive'])->default('active');
            $table->unsignedBigInteger('session_id');

            // ❌ احذف after()
            $table->unsignedBigInteger('user_id')->nullable();

            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('school_sessions')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

    }
    public function down(): void {
        Schema::dropIfExists('staff');
        Schema::table('staff', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
