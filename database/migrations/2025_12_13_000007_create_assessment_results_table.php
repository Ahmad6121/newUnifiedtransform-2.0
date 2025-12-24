<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('assessment_results')) return;

        Schema::create('assessment_results', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('assessment_id');
            $table->unsignedBigInteger('student_id');

            $table->decimal('marks_obtained', 8, 2)->nullable();

            // (اختياري) إذا بدك تحفظ مين قيّم:
            // $table->unsignedBigInteger('graded_by')->nullable();

            $table->timestamps();

            $table->unique(['assessment_id', 'student_id']);
            $table->index('assessment_id');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_results');
    }
};
