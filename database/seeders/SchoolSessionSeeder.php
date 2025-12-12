<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolSession;

class SchoolSessionSeeder extends Seeder
{
    public function run(): void
    {
        // سنة دراسية واحدة
        SchoolSession::firstOrCreate([
            'session_name' => '2025 - 2026',
        ]);
    }
}
