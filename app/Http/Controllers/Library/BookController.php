<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::with('session')->paginate(20);
        return view('library.books.index', compact('books'));
    }

    public function create()
    {
        return view('library.books.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'=>'required|string|max:255',
            'author'=>'nullable|string|max:255',
            'isbn'=>'nullable|string|max:100|unique:books,isbn',
            'quantity'=>'required|integer|min:0',
            'available_quantity'=>'nullable|integer|min:0',
            'shelf'=>'nullable|string|max:50',
            'publisher'=>'nullable|string|max:100',
            'published_year'=>'nullable|integer|min:1900|max:2100',
        ]);
        $data['available_quantity'] = $data['available_quantity'] ?? $data['quantity'];
        $data['session_id'] = \App\Models\SchoolSession::latest()->value('id');
        Book::create($data);

        return redirect()->route('library.books.index')->with('status','Book added');
    }

    public function edit(Book $book)
    {
        return view('library.books.edit', compact('book'));
    }

    public function update(Request $request, Book $book)
    {
        $data = $request->validate([
            'title'=>'required|string|max:255',
            'author'=>'nullable|string|max:255',
            'isbn'=>'nullable|string|max:100|unique:books,isbn,'.$book->id,
            'quantity'=>'required|integer|min:0',
            'available_quantity'=>'required|integer|min:0',
            'shelf'=>'nullable|string|max:50',
            'publisher'=>'nullable|string|max:100',
            'published_year'=>'nullable|integer|min:1900|max:2100',
        ]);
        $book->update($data);
        return redirect()->route('library.books.index')->with('status','Book updated');
    }

    public function destroy(Book $book)
    {
        $book->delete();
        return back()->with('status','Book deleted');
    }
}
