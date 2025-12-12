<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class StudentParentInfoSeeder extends Seeder
{
    public function run(): void
    {
        // نجيب كل الطلاب
        $students = User::where('role', 'student')->get();

        if ($students->isEmpty()) {
            $this->command->warn('⚠️ لا يوجد طلاب — شغّل UserSeeder أولاً.');
            return;
        }

        // أعمدة جدول student_parent_infos (عشان ما نضيف أعمدة مش موجودة)
        $columns = Schema::getColumnListing('student_parent_infos');
        $now = now();
        $count = 0;

        foreach ($students as $student) {

            // 🆕 1) إنشاء إيميل نظيف لحساب ولي الأمر
            // مثال: parent_ahmad_id39@smartschool.com
            $safeFirstName = strtolower(
                preg_replace('/[^a-z0-9]+/i', '', $student->first_name ?? 'student')
            );

            $parentEmail = 'parent_' . $safeFirstName . '_id' . $student->id . '@smartschool.com';

            // 2) إنشاء/جلب حساب ولي الأمر (Parent User)
            $parentUser = User::firstOrCreate(
                ['email' => $parentEmail],
                [
                    'first_name'  => 'Parent of ' . ($student->first_name ?? 'Student'),
                    'last_name'   => $student->last_name ?? '',
                    'gender'      => 'male',
                    'nationality' => $student->nationality ?? 'Jordanian',
                    'phone'       => '07' . random_int(700000000, 799999999),
                    'role'        => 'parent',
                    'password'    => Hash::make('password'), // باسورد افتراضي
                    'address'     => $student->address ?? 'Amman, Jordan',
                    'address2'    => $student->address2 ?? 'N/A',
                    'city'        => $student->city ?? 'Amman',
                    'zip'         => $student->zip ?? '11118',
                ]
            );

            // إعطاء role "parent" من Spatie (لو موجود)
            if (method_exists($parentUser, 'hasRole') && !$parentUser->hasRole('parent')) {
                $parentUser->assignRole('parent');
            }

            // 🧩 3) تجهيز بيانات جدول student_parent_infos
            $data = [
                'student_id'      => $student->id,
                'father_name'     => 'Father of ' . ($student->first_name ?? $student->last_name ?? 'Student'),
                'father_phone'    => '07' . random_int(700000000, 799999999),
                'mother_name'     => 'Mother of ' . ($student->first_name ?? $student->last_name ?? 'Student'),
                'mother_phone'    => '07' . random_int(800000000, 899999999),
                'guardian_name'   => 'Guardian of ' . ($student->first_name ?? $student->last_name ?? 'Student'),
                'guardian_phone'  => '07' . random_int(600000000, 699999999),
                'parent_address'  => 'Amman, Jordan',
                'occupation'      => 'Employee',
                'parent_user_id'  => $parentUser->id, // ربط الطالب بحساب الأب
                'created_at'      => $now,
                'updated_at'      => $now,
            ];

            // فلترة الأعمدة الموجودة فعلياً في الجدول
            $filtered = array_intersect_key($data, array_flip($columns));

            // إدخال/تحديث بيانات أولياء الأمور
            DB::table('student_parent_infos')->updateOrInsert(
                ['student_id' => $student->id],
                $filtered
            );

            $count++;
        }

        $this->command->info("✅ StudentParentInfoSeeder: تم إنشاء/تحديث بيانات أولياء الأمور + حسابات parents لـ {$count} طالب.");
    }
}
