<?php

namespace App\Http\Controllers\Library;


use App\Models\Book;
use App\Http\Controllers\Controller;
use App\Models\SchoolSession;
use Illuminate\Http\Request;

class BookController extends Controller
{
    private function currentSessionId(): ?int
    {
        if (method_exists($this, 'getSchoolCurrentSession')) {
            $id = $this->getSchoolCurrentSession();
            if ($id) return $id;
        }
        return SchoolSession::latest()->value('id');
    }

    public function index(Request $request)
    {
        $q = Book::where('session_id', $this->currentSessionId());

        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function($x) use ($s){
                $x->where('title','like',"%$s%")
                    ->orWhere('author','like',"%$s%")
                    ->orWhere('isbn','like',"%$s%");
            });
        }

        $books = $q->orderBy('id','desc')->paginate(20)->withQueryString();
        return view('library.books.index', compact('books'));
    }

    public function create()
    {
        return view('library.books.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'           => 'required|string|max:255',
            'author'          => 'nullable|string|max:255',
            'isbn'            => 'nullable|string|max:255|unique:books,isbn',
            'quantity'        => 'required|integer|min:0',
            'shelf'           => 'nullable|string|max:255',
            'publisher'       => 'nullable|string|max:255',
            'published_year'  => 'nullable|integer|min:0|max:2100',
        ]);

        $data['session_id'] = $this->currentSessionId();
        $data['available_quantity'] = $data['quantity']; // أول ما نضيف كتاب

        Book::create($data);
        return redirect()->route('library.books.index')->with('success', 'Book created.');
    }

    public function edit(Book $book)
    {
        return view('library.books.edit', compact('book'));
    }

    public function update(Request $request, Book $book)
    {
        $data = $request->validate([
            'title'           => 'required|string|max:255',
            'author'          => 'nullable|string|max:255',
            'isbn'            => 'nullable|string|max:255|unique:books,isbn,' . $book->id,
            'quantity'        => 'required|integer|min:0',
            'shelf'           => 'nullable|string|max:255',
            'publisher'       => 'nullable|string|max:255',
            'published_year'  => 'nullable|integer|min:0|max:2100',
        ]);

        // تعديل الكمية بدون ما نخرب available
        $oldQty = (int)$book->quantity;
        $oldAvail = (int)$book->available_quantity;
        $newQty = (int)$data['quantity'];

        if ($newQty !== $oldQty) {
            $diff = $newQty - $oldQty;
            $newAvail = $oldAvail + $diff;
            if ($newAvail < 0) $newAvail = 0;
            if ($newAvail > $newQty) $newAvail = $newQty;
            $data['available_quantity'] = $newAvail;
        }

        $book->update($data);
        return redirect()->route('library.books.index')->with('success', 'Book updated.');
    }

    public function destroy(Book $book)
    {
        $book->delete();
        return back()->with('success', 'Book deleted.');
    }
}
