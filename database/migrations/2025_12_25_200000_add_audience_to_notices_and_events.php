<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAudienceToNotices extends Migration
{
    public function up()
    {
        if (Schema::hasTable('notices')) {
            Schema::table('notices', function (Blueprint $table) {
                if (!Schema::hasColumn('notices', 'audience_type')) {
                    $table->string('audience_type')->default('all'); // all|roles|users
                }
                if (!Schema::hasColumn('notices', 'audience_roles')) {
                    $table->json('audience_roles')->nullable(); // ["teacher","parent"]
                }
                if (!Schema::hasColumn('notices', 'audience_users')) {
                    $table->json('audience_users')->nullable(); // [2,5,9]
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('notices')) {
            Schema::table('notices', function (Blueprint $table) {
                if (Schema::hasColumn('notices', 'audience_type')) $table->dropColumn('audience_type');
                if (Schema::hasColumn('notices', 'audience_roles')) $table->dropColumn('audience_roles');
                if (Schema::hasColumn('notices', 'audience_users')) $table->dropColumn('audience_users');
            });
        }
    }
}
