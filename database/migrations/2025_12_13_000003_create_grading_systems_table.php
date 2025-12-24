<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('grading_systems', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Example: A-F, Percentage, etc.
            $table->unsignedBigInteger('class_id')->nullable(); // optional (per grade)
            $table->unsignedBigInteger('semester_id')->nullable(); // optional
            $table->timestamps();

            $table->foreign('class_id')->references('id')->on('school_classes')->nullOnDelete();
            $table->foreign('semester_id')->references('id')->on('semesters')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grading_systems');
    }
};
