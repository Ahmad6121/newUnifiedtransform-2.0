<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookIssue;
use App\Models\User;
use Illuminate\Http\Request;

class BookIssueController extends Controller
{
    public function index()
    {
        $issues = BookIssue::with(['book','student'])->latest()->paginate(20);
        return view('library.issues.index', compact('issues'));
    }

    public function create()
    {
        $books = Book::where('available_quantity','>',0)->get();
        $students = User::role('student')->get();
        return view('library.issues.create', compact('books','students'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'book_id'=>'required|exists:books,id',
            'student_id'=>'required|exists:users,id',
            'issue_date'=>'required|date',
            'due_date'=>'nullable|date|after_or_equal:issue_date',
            'notes'=>'nullable|string',
        ]);
        $issue = BookIssue::create($data);
        $issue->book()->decrement('available_quantity');
        return redirect()->route('library.issues.index')->with('status','Book issued');
    }

    public function return(BookIssue $issue)
    {
        $issue->update(['return_date'=>now(),'status'=>'returned']);
        $issue->book()->increment('available_quantity');
        return back()->with('status','Book returned');
    }
    public function edit(BookIssue $issue)
    {
        return view('library.issues.edit', compact('issue'));
    }

    public function update(Request $request, BookIssue $issue)
    {
        $data = $request->validate([
            'issue_date'  => 'required|date',
            'due_date'    => 'nullable|date|after_or_equal:issue_date',
            'status'      => 'required|in:issued,returned,overdue',
            'notes'       => 'nullable|string|max:2000',
        ]);

        $issue->update($data);

        // إذا تم تعديل الحالة إلى returned نزيد المتاح
        if ($issue->status === 'returned' && !$issue->return_date) {
            $issue->update(['return_date'=>now()]);
            $issue->book()->increment('available_quantity');
        }

        return redirect()->route('library.issues.index')->with('status','Issue updated');
    }

}
