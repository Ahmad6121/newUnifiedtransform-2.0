<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalaryProfilesTable extends Migration
{
    public function up()
    {
        Schema::create('salary_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique(); // teacher/staff
            $table->decimal('base_salary', 10, 2)->default(0);
            $table->string('pay_cycle', 20)->default('monthly'); // monthly
            $table->date('effective_from')->nullable();
            $table->boolean('active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('salary_profiles');
    }
}
