<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('isbn')->unique()->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('available_quantity')->default(0);
            $table->string('shelf')->nullable();
            $table->string('publisher')->nullable();
            $table->integer('published_year')->nullable();
            $table->unsignedBigInteger('session_id');
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('school_sessions')->cascadeOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('books');
    }
};
