<?php

// database/seeders/JobTitleSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobTitle;

class JobTitleSeeder extends Seeder
{
    public function run(): void
    {
        $titles = ['Teacher','Accountant','Librarian','Administrator','Driver','Cleaner'];
        foreach($titles as $title){
            JobTitle::firstOrCreate(['name'=>$title]);
        }
    }
}

