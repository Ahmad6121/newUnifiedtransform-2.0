<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolSession;
use App\Models\SchoolClass;

class SchoolClassSeeder extends Seeder
{
    public function run(): void
    {
        $session = SchoolSession::orderByDesc('id')->first();
        if (!$session) return;

        for ($g = 1; $g <= 12; $g++) {
            SchoolClass::firstOrCreate([
                'class_name' => "Grade {$g}",
                'session_id' => $session->id,
            ]);
        }
    }
}

