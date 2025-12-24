<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('marks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('exam_id')->nullable(); // optional

            $table->decimal('mark', 8, 2)->default(0);

            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('course_id')->references('id')->on('courses')->cascadeOnDelete();
            $table->foreign('exam_id')->references('id')->on('exams')->nullOnDelete();

            $table->unique(['student_id', 'course_id', 'exam_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marks');
    }
};
