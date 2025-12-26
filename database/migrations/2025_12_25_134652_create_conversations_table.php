<?php
// database/migrations/xxxx_xx_xx_create_conversations_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConversationsTable extends Migration {
    public function up() {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('general');
            // student_teacher | parent_teacher | parent_finance | teacher_teacher | finance_any | staff_finance | general
            $table->string('subject')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }
    public function down() { Schema::dropIfExists('conversations'); }
}
