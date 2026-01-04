<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddEmployeeMorphToPayrollsTable extends Migration
{
    public function up()
    {
        /**
         * ✅ لو جدول payrolls غير موجود: ننشئه كامل (هذا اللي ناقص عندك)
         */
        if (!Schema::hasTable('payrolls')) {
            Schema::create('payrolls', function (Blueprint $table) {
                $table->id();

                // ✅ الموظف: يمكن يكون User (Teacher/Accountant/Staff) أو جدول Staff مستقل
                $table->string('employee_type')->nullable();
                $table->unsignedBigInteger('employee_id')->nullable();
                $table->string('employee_ref')->nullable();

                // ✅ بيانات الراتب
                $table->string('title');
                $table->decimal('amount', 12, 2);
                $table->date('payroll_date');
                $table->text('notes')->nullable();

                // ✅ من أنشأ السجل (اختياري)
                $table->unsignedBigInteger('created_by')->nullable();

                $table->timestamps();

                $table->index(['employee_type', 'employee_id'], 'payrolls_employee_morph_idx');
            });

            return; // ✅ خلصنا
        }

        /**
         * ✅ لو جدول payrolls موجود: نضيف الأعمدة الجديدة فقط
         */
        Schema::table('payrolls', function (Blueprint $table) {
            if (!Schema::hasColumn('payrolls', 'employee_type')) {
                $table->string('employee_type')->nullable()->after('id');
            }
            if (!Schema::hasColumn('payrolls', 'employee_id')) {
                $table->unsignedBigInteger('employee_id')->nullable()->after('employee_type');
            }
            if (!Schema::hasColumn('payrolls', 'employee_ref')) {
                $table->string('employee_ref')->nullable()->after('employee_id');
            }

            // ✅ أعمدة أساسية لو كانت ناقصة (احتياط)
            if (!Schema::hasColumn('payrolls', 'title')) {
                $table->string('title')->nullable();
            }
            if (!Schema::hasColumn('payrolls', 'amount')) {
                $table->decimal('amount', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('payrolls', 'payroll_date')) {
                $table->date('payroll_date')->nullable();
            }
            if (!Schema::hasColumn('payrolls', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (!Schema::hasColumn('payrolls', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable();
            }

            // Index لو مش موجود
            // (لو رح يطلع خطأ “duplicate key name” احذفه)
            try {
                $table->index(['employee_type', 'employee_id'], 'payrolls_employee_morph_idx');
            } catch (\Exception $e) {}
        });

        // ✅ Backfill لو عندك بيانات قديمة (teacher_id أو user_id)
        if (Schema::hasColumn('payrolls', 'teacher_id')) {
            DB::statement("UPDATE payrolls SET employee_type='App\\\\Models\\\\User', employee_id=teacher_id WHERE employee_id IS NULL AND teacher_id IS NOT NULL");
        } elseif (Schema::hasColumn('payrolls', 'user_id')) {
            DB::statement("UPDATE payrolls SET employee_type='App\\\\Models\\\\User', employee_id=user_id WHERE employee_id IS NULL AND user_id IS NOT NULL");
        }
    }

    public function down()
    {
        // ✅ إذا جدول payrolls تم إنشاؤه من هذا المايغريشن في بيئتك (وهو حالتك الآن)
        // الأفضل نتركه، أو إذا بدك فعلاً يرجع: احذف الجدول كله.
        // أنا بخليه آمن: إذا موجود، احذف الأعمدة الجديدة فقط (وما بحذف الجدول)
        if (!Schema::hasTable('payrolls')) return;

        Schema::table('payrolls', function (Blueprint $table) {
            if (Schema::hasColumn('payrolls', 'employee_ref')) $table->dropColumn('employee_ref');
            if (Schema::hasColumn('payrolls', 'employee_id'))  $table->dropColumn('employee_id');
            if (Schema::hasColumn('payrolls', 'employee_type')) $table->dropColumn('employee_type');

            // ملاحظة: ما بحذف title/amount/payroll_date لأنه ممكن تكون مستخدمة فعلياً
        });
    }
}
