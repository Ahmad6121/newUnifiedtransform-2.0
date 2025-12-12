<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('routines', function (Blueprint $table) {
            $table->id();

            // 🆕 ربط الحصص بالسنة الدراسية
            $table->unsignedBigInteger('session_id');
            $table->foreign('session_id')->references('id')->on('school_sessions')->cascadeOnDelete();

            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('teacher_id')->nullable();

            $table->string('day'); // مثل Sunday, Monday...
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room_no')->nullable();

            $table->timestamps();

            $table->foreign('class_id')->references('id')->on('school_classes')->cascadeOnDelete();
            $table->foreign('section_id')->references('id')->on('sections')->cascadeOnDelete();
            $table->foreign('course_id')->references('id')->on('courses')->cascadeOnDelete();
            $table->foreign('teacher_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('routines');
    }
};
