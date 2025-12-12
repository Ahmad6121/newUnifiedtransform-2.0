<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('exam_id');
            $table->string('question_type'); // mcq | true_false | essay | fill_blank | hotspot
            $table->text('question_text');
            $table->string('image_path')->nullable();
            $table->float('marks')->default(0);
            $table->unsignedInteger('order')->default(1);

            // hotspot optional
            $table->integer('hotspot_x')->nullable();
            $table->integer('hotspot_y')->nullable();
            $table->integer('hotspot_radius')->nullable();

            $table->timestamps();

            $table->index(['exam_id', 'question_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
    }
};
