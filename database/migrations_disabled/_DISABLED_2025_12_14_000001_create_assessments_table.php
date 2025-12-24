<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('assessments')) return;

        Schema::create('assessments', function (Blueprint $table) {
            $table->id();

            // scope
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('semester_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->unsignedBigInteger('teacher_id')->nullable(); // owner teacher
            $table->unsignedBigInteger('created_by')->nullable(); // admin/teacher creator (optional)

            // basic info
            $table->string('title');
            $table->text('description')->nullable();

            // kind: exam/quiz/assignment/project/research/oral
            $table->string('kind')->default('exam');

            // mode: online/manual
            $table->string('mode')->default('manual');

            // grading settings
            $table->decimal('total_marks', 8, 2)->default(100);
            $table->decimal('passing_marks', 8, 2)->default(50);
            $table->decimal('weight_percent', 5, 2)->nullable(); // 0-100 (supports decimals)

            // schedule
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();

            // online settings
            $table->unsignedInteger('attempts_allowed')->default(1);
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('shuffle_options')->default(false);

            // status
            $table->string('status')->default('draft'); // draft|published|closed
            $table->boolean('results_published')->default(false);

            $table->timestamps();

            $table->index(['session_id', 'semester_id', 'class_id', 'section_id', 'course_id'], 'assessments_scope_idx');
            $table->index(['teacher_id', 'status', 'mode'], 'assessments_teacher_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
