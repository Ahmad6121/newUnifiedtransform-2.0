<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicSetting;

class AcademicSettingSeeder extends Seeder
{
    public function run(): void
    {
        AcademicSetting::firstOrCreate(
            ['id' => 1],
            [
                'attendance_type'        => 'section', // أو 'daily' إذا تحب تغيّر الإعداد الافتراضي
                'marks_submission_status'=> 'off',
                'created_at'             => now(),
                'updated_at'             => now(),
            ]
        );

        $this->command->info('✅ AcademicSettingSeeder: تم إنشاء الإعدادات الأكاديمية بنجاح.');
    }
}
