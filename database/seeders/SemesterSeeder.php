<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolSession;
use App\Models\Semester;

class SemesterSeeder extends Seeder
{
    public function run(): void
    {
        $session = SchoolSession::orderByDesc('id')->first();
        if (!$session) return;

        $rows = [
            [
                'semester_name' => 'First Semester',
                'start_date'    => '2025-09-01',
                'end_date'      => '2026-01-15',
                'session_id'    => $session->id,
            ],
            [
                'semester_name' => 'Second Semester',
                'start_date'    => '2026-02-01',
                'end_date'      => '2026-06-15',
                'session_id'    => $session->id,
            ],
        ];

        foreach ($rows as $row) {
            Semester::firstOrCreate(
                ['semester_name' => $row['semester_name'], 'session_id' => $session->id],
                $row
            );
        }
    }
}
