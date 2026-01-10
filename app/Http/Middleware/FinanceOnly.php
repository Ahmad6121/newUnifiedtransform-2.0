<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FinanceOnly
{
    public function handle(Request $request, Closure $next)
    {
        $u = $request->user();
        if (!$u) abort(403);

        $roleCol = strtolower((string)($u->role ?? ''));

        $isAdmin = in_array($roleCol, ['admin', 'super admin', 'super_admin'], true);
        $isAccountant = ($roleCol === 'accountant');

        // Spatie roles (لو موجود)
        if (method_exists($u, 'hasRole')) {
            $isAdmin = $isAdmin || $u->hasRole('admin') || $u->hasRole('Super Admin');
            $isAccountant = $isAccountant || $u->hasRole('accountant');
        }

        if (!$isAdmin && !$isAccountant) abort(403);

        return $next($request);
    }
}
