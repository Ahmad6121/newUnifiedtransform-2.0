<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exam_answers', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('attempt_id');
            $table->unsignedBigInteger('question_id');
            $table->unsignedInteger('student_id');

            $table->unsignedBigInteger('selected_option_id')->nullable();
            $table->text('answer_text')->nullable();

            $table->integer('hotspot_x')->nullable();
            $table->integer('hotspot_y')->nullable();

            $table->float('marks_obtained')->default(0);
            $table->boolean('is_auto_graded')->default(false);

            $table->timestamps();

            $table->unique(['attempt_id', 'question_id'], 'attempt_question_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_answers');
    }
};
