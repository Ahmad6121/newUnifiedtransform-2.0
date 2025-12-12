<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('exam_id');
            $table->unsignedInteger('student_id');

            $table->dateTime('started_at')->nullable();
            $table->dateTime('submitted_at')->nullable();

            $table->string('status')->default('in_progress'); // in_progress | submitted | graded

            $table->float('auto_marks')->default(0);
            $table->float('manual_marks')->default(0);
            $table->float('total_marks_obtained')->default(0);

            $table->timestamps();

            $table->index(['exam_id', 'student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};
