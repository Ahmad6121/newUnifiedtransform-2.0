<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('grade_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grading_system_id');

            $table->decimal('min_percent', 5, 2);
            $table->decimal('max_percent', 5, 2);

            $table->string('grade');   // A, B, C...
            $table->string('remark')->nullable();

            $table->timestamps();

            $table->foreign('grading_system_id')->references('id')->on('grading_systems')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_rules');
    }
};
