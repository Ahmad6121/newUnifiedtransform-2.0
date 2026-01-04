<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookIssue;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookIssueController extends Controller
{
    public function create()
    {
        $books = Book::orderBy('title')->get();
        $students = User::where('role', 'student')->orderBy('first_name')->get(); // لو عندك spatie ممكن نغيرها
        return view('library.issues.create', compact('books','students'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'book_id'    => 'required|exists:books,id',
            'student_id' => 'required|exists:users,id',
            'issue_date' => 'required|date',
            'due_date'   => 'nullable|date|after_or_equal:issue_date',
            'notes'      => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            $book = Book::where('id', $data['book_id'])->lockForUpdate()->first();

            if ($book->available_quantity <= 0) {
                abort(422, 'This book is not available right now.');
            }

            $book->decrement('available_quantity');

            BookIssue::create([
                'book_id'     => $data['book_id'],
                'student_id'  => $data['student_id'],
                'issue_date'  => $data['issue_date'],
                'due_date'    => $data['due_date'] ?? null,
                'status'      => 'issued',
                'notes'       => $data['notes'] ?? null,
            ]);
        });

        return redirect()->route('library.books.index')->with('success', 'Book issued.');
    }

    public function returnBook(Request $request, BookIssue $issue)
    {
        DB::transaction(function () use ($issue) {
            $issue = BookIssue::where('id', $issue->id)->lockForUpdate()->first();
            if ($issue->status === 'returned') return;

            $book = Book::where('id', $issue->book_id)->lockForUpdate()->first();

            $issue->update([
                'status' => 'returned',
                'return_date' => now()->toDateString(),
            ]);

            // نرجع نسخة (مع حماية ما تتجاوز quantity)
            $book->available_quantity = min((int)$book->quantity, (int)$book->available_quantity + 1);
            $book->save();
        });

        return back()->with('success', 'Book returned.');
    }
}
