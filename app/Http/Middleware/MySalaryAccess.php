<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MySalaryAccess
{
    public function handle(Request $request, Closure $next)
    {
        $u = $request->user();
        if (!$u) abort(403);

        $roleCol = strtolower((string)($u->role ?? ''));

        $isTeacher = ($roleCol === 'teacher');
        $isAdmin = in_array($roleCol, ['admin', 'super admin', 'super_admin'], true);
        $isAccountant = ($roleCol === 'accountant');

        if (method_exists($u, 'hasRole')) {
            $isTeacher = $isTeacher || $u->hasRole('teacher');
            $isAdmin = $isAdmin || $u->hasRole('admin') || $u->hasRole('Super Admin');
            $isAccountant = $isAccountant || $u->hasRole('accountant');
        }

        abort_unless($isTeacher || $isAdmin || $isAccountant, 403);

        return $next($request);
    }
}
