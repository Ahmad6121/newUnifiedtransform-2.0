<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('assessment_attempts')) return;

        Schema::create('assessment_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_id');
            $table->unsignedBigInteger('student_id');

            $table->dateTime('started_at')->nullable();
            $table->dateTime('submitted_at')->nullable();

            $table->string('status')->default('in_progress'); // in_progress|submitted|graded

            $table->float('auto_marks')->default(0);
            $table->float('manual_marks')->default(0);
            $table->float('total_marks_obtained')->default(0);

            $table->timestamps();

            $table->index(['assessment_id', 'student_id', 'status'], 'attempts_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_attempts');
    }
};
