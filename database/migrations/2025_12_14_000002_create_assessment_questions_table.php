<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('assessment_questions')) return;

        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_id');

            // mcq | true_false | essay | fill_blank | hotspot
            $table->string('question_type');
            $table->text('question_text');

            $table->string('image_path')->nullable();

            $table->float('marks')->default(0);
            $table->unsignedInteger('order')->default(1);

            // fill blank (optional correct answer)
            $table->string('correct_text')->nullable();

            // hotspot
            $table->integer('hotspot_x')->nullable();
            $table->integer('hotspot_y')->nullable();
            $table->integer('hotspot_radius')->nullable();

            $table->timestamps();

            $table->index(['assessment_id', 'question_type'], 'assessment_questions_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_questions');
    }
};
