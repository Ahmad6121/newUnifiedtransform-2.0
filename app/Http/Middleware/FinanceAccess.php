<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FinanceAccess
{
    public function handle(Request $request, Closure $next)
    {
        $u = auth()->user();
        if (!$u) {
            abort(403);
        }

        $roleCol = $u->role ?? '';

        $isAdmin = (method_exists($u, 'isAdmin') && $u->isAdmin())
            || $roleCol === 'admin'
            || (method_exists($u, 'hasRole') && $u->hasRole('admin'));

        $isAccountant = (method_exists($u, 'isAccountant') && $u->isAccountant())
            || $roleCol === 'accountant'
            || (method_exists($u, 'hasRole') && $u->hasRole('accountant'));

        if ($isAdmin || $isAccountant) {
            return $next($request);
        }

        abort(403);
    }
}
