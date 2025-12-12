<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentUserIdToStudentParentInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // نتأكد إن الجدول موجود
        if (Schema::hasTable('student_parent_infos')) {
            // ونتأكد إن العمود مش مضاف من قبل
            if (!Schema::hasColumn('student_parent_infos', 'parent_user_id')) {
                Schema::table('student_parent_infos', function (Blueprint $table) {
                    $table->unsignedBigInteger('parent_user_id')
                        ->nullable()
                        ->after('student_id');

                    $table->foreign('parent_user_id')
                        ->references('id')
                        ->on('users')
                        ->onDelete('set null');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('student_parent_infos')) {
            if (Schema::hasColumn('student_parent_infos', 'parent_user_id')) {
                Schema::table('student_parent_infos', function (Blueprint $table) {
                    $table->dropForeign(['parent_user_id']);
                    $table->dropColumn('parent_user_id');
                });
            }
        }
    }
}

