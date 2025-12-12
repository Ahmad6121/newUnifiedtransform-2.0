<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolSession;
use App\Models\SchoolClass;
use App\Models\Section;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $session = SchoolSession::orderByDesc('id')->first();
        if (!$session) return;

        $names = ['Section A', 'Section B'];

        foreach (SchoolClass::where('session_id', $session->id)->get() as $class) {
            foreach ($names as $i => $name) {
                Section::firstOrCreate(
                    [
                        'section_name' => $name,
                        'class_id'     => $class->id,
                        'session_id'   => $session->id,
                    ],
                    [
                        // رقم قاعة بسيط متغير حسب الصف/الشعبة
                        'room_no'      => (string)(100 + $i + ($class->id % 50)),
                    ]
                );
            }
        }
    }
}

