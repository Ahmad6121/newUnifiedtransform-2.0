<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('assessment_question_options')) return;

        Schema::create('assessment_question_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->timestamps();

            $table->index(['question_id', 'is_correct'], 'question_options_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_question_options');
    }
};
