<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookIssue;
use App\Models\SchoolSession;
use Illuminate\Http\Request;

class LibraryReportController extends Controller
{
    private function currentSessionId()
    {
        if (session()->has('browse_session_id')) {
            return (int) session('browse_session_id');
        }

        $latest = SchoolSession::orderBy('id', 'desc')->first();
        return $latest ? (int) $latest->id : null;
    }

    public function index(Request $request)
    {
        $sessionId = $this->currentSessionId();

        // Counters
        $totalBooks = Book::query()
            ->when($sessionId, function ($q) use ($sessionId) {
                $q->where('session_id', $sessionId);
            })
            ->count();

        $totalCopies = Book::query()
            ->when($sessionId, function ($q) use ($sessionId) {
                $q->where('session_id', $sessionId);
            })
            ->sum('quantity');

        $availableCopies = Book::query()
            ->when($sessionId, function ($q) use ($sessionId) {
                $q->where('session_id', $sessionId);
            })
            ->sum('available_quantity');

        $issuedCount = BookIssue::where('status', 'issued')->count();
        $overdueCount = BookIssue::where('status', 'overdue')->count();
        $returnedCount = BookIssue::where('status', 'returned')->count();

        // Latest issues
        $latestIssues = BookIssue::with(['book', 'student'])
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        return view('library.reports.index', compact(
            'sessionId',
            'totalBooks',
            'totalCopies',
            'availableCopies',
            'issuedCount',
            'overdueCount',
            'returnedCount',
            'latestIssues'
        ));
    }
}
