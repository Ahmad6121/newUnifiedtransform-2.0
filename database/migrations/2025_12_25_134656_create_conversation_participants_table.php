<?php
// database/migrations/xxxx_xx_xx_create_conversation_participants_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConversationParticipantsTable extends Migration {
    public function up() {
        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('last_read_at')->nullable()->index();
            $table->boolean('is_admin_observer')->default(false); // لو الأدمن دخل "مراقب"
            $table->timestamps();

            $table->unique(['conversation_id', 'user_id']);

            $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
    public function down() { Schema::dropIfExists('conversation_participants'); }
}
