<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE users MODIFY gender VARCHAR(50) NULL");
    }

    public function down()
    {
        // رجّعها NOT NULL (اختياري)
        DB::statement("ALTER TABLE users MODIFY gender VARCHAR(50) NOT NULL");
    }
};
