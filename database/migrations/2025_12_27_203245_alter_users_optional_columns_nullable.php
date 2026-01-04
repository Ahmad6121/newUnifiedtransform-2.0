<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // اجعل أعمدة الطالب Optional حتى نقدر ننشئ Parent بدونها
        DB::statement("ALTER TABLE users MODIFY gender VARCHAR(50) NULL");
        DB::statement("ALTER TABLE users MODIFY nationality VARCHAR(100) NULL");

        DB::statement("ALTER TABLE users MODIFY address2 VARCHAR(255) NULL");
        DB::statement("ALTER TABLE users MODIFY city VARCHAR(100) NULL");
        DB::statement("ALTER TABLE users MODIFY zip VARCHAR(50) NULL");

        DB::statement("ALTER TABLE users MODIFY birthday DATE NULL");
        DB::statement("ALTER TABLE users MODIFY religion VARCHAR(100) NULL");
        DB::statement("ALTER TABLE users MODIFY blood_type VARCHAR(50) NULL");

        DB::statement("ALTER TABLE users MODIFY photo VARCHAR(255) NULL");
    }

    public function down(): void
    {
        // اختياري: اتركها فاضية أو رجّعها NOT NULL حسب رغبتك
    }
};
