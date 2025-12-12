<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BookIssue;
use App\Models\Book;
use App\Models\User;
use Carbon\Carbon;

class BookIssueSeeder extends Seeder
{
    public function run(): void
    {
        $book = Book::first();
        $student = User::where('role', 'student')->first();

        if ($book && $student) {
            BookIssue::firstOrCreate([
                'book_id' => $book->id,
                'student_id' => $student->id,
                'issue_date' => Carbon::now()->subDays(2),
            ], [
                'due_date' => Carbon::now()->addDays(7),
                'return_date' => null,
                'status' => 'issued',
                'notes' => 'This is a seeded book issue for testing.',
            ]);
        }
    }
}
