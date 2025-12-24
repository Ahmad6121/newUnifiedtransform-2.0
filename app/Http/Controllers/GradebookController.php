<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Promotion;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class GradebookController extends Controller
{
    private function currentSessionId(): int
    {
        if (session()->has('browse_session_id')) {
            return (int) session('browse_session_id');
        }

        return (int) (SchoolSession::latest()->value('id') ?? 0);
    }

    private function userDisplayName($user): string
    {
        // ✅ works with: name, full_name, first_name/last_name, username, fallback
        if (!empty($user->name)) return $user->name;
        if (!empty($user->full_name)) return $user->full_name;

        $fn = $user->first_name ?? null;
        $ln = $user->last_name ?? null;
        $combo = trim(($fn ?? '') . ' ' . ($ln ?? ''));
        if (!empty($combo)) return $combo;

        if (!empty($user->username)) return $user->username;

        return 'Student #' . ($user->id ?? '-');
    }

    private function orderUsersQuery($query)
    {
        // ✅ order by existing column (your users table may not have "name")
        $usersTable = (new User())->getTable();
        $cols = Schema::getColumnListing($usersTable);

        if (in_array('name', $cols)) {
            $query->orderBy('name', 'asc');
        } elseif (in_array('full_name', $cols)) {
            $query->orderBy('full_name', 'asc');
        } elseif (in_array('first_name', $cols)) {
            $query->orderBy('first_name', 'asc');
        } elseif (in_array('firstname', $cols)) {
            $query->orderBy('firstname', 'asc');
        } elseif (in_array('username', $cols)) {
            $query->orderBy('username', 'asc');
        } else {
            $query->orderBy('id', 'asc');
        }

        return $query;
    }

    /**
     * Gradebook Home: list courses (Admin sees all, Teacher sees all too unless you want to restrict later)
     */
    public function index()
    {
        if (!in_array(Auth::user()->role, ['admin', 'teacher'])) {
            abort(403);
        }

        $sessionId = $this->currentSessionId();

        // Courses table عندك: course_name, course_type, class_id, semester_id, session_id
        $courses = Course::where('session_id', $sessionId)
            ->orderBy('course_name', 'asc')
            ->get();

        return view('gradebook.index', compact('courses'));
    }

    /**
     * Gradebook for one course
     */
    public function course($courseId)
    {
        if (!in_array(Auth::user()->role, ['admin', 'teacher'])) {
            abort(403);
        }

        $sessionId = $this->currentSessionId();

        $course = Course::where('id', $courseId)->firstOrFail();

        // تأكد ان الكورس من نفس السيشن
        if ((int)$course->session_id !== (int)$sessionId) {
            // إذا عندك browse session ممكن يختلف، خلّيه بسيط:
            // abort(404);
        }

        $class = null;
        if (!empty($course->class_id)) {
            $class = SchoolClass::where('id', $course->class_id)->first();
        }

        // students in this class for current session (Promotions)
        $studentIds = Promotion::where('session_id', $sessionId)
            ->when(!empty($course->class_id), function ($q) use ($course) {
                $q->where('class_id', $course->class_id);
            })
            ->pluck('student_id')
            ->unique()
            ->values()
            ->toArray();

        $students = collect();
        if (!empty($studentIds)) {
            $q = User::whereIn('id', $studentIds);
            $q = $this->orderUsersQuery($q);
            $students = $q->get();
        }

        // Pass display_name to view easily
        $students->transform(function ($s) {
            $s->display_name = $this->userDisplayName($s);
            return $s;
        });

        return view('gradebook.course', compact('course', 'class', 'students'));
    }
}
