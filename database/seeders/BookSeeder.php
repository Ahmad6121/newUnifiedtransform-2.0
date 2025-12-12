<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        $books = [
            [
                'title' => 'Introduction to Programming',
                'author' => 'John Doe',
                'isbn' => '978-1234567890',
                'quantity' => 5,
                'available_quantity' => 5,
                'shelf' => 'A1',
                'publisher' => 'TechBooks',
                'published_year' => 2020,
                'session_id' => 1,
            ],
            [
                'title' => 'Advanced Mathematics',
                'author' => 'Jane Smith',
                'isbn' => '978-0987654321',
                'quantity' => 3,
                'available_quantity' => 3,
                'shelf' => 'B2',
                'publisher' => 'EduBooks',
                'published_year' => 2021,
                'session_id' => 1,
            ],
            [
                'title' => 'Physics Fundamentals',
                'author' => 'Albert Newton',
                'isbn' => '978-4567891230',
                'quantity' => 4,
                'available_quantity' => 4,
                'shelf' => 'C3',
                'publisher' => 'SciencePress',
                'published_year' => 2019,
                'session_id' => 1,
            ],
        ];

        foreach ($books as $book) {
            Book::firstOrCreate(['isbn' => $book['isbn']], $book);
        }
    }
}
