<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('semester_id')->nullable();

            $table->timestamp('starts')->nullable();
            $table->timestamp('ends')->nullable();

            // Online fields
            $table->boolean('is_online')->default(false);
            $table->unsignedInteger('duration_minutes')->nullable(); // null = no timer
            $table->unsignedInteger('max_attempts')->default(1);

            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('courses')->cascadeOnDelete();
            $table->foreign('semester_id')->references('id')->on('semesters')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
