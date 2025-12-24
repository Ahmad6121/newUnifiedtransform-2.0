<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Schema;

class ReportsController extends Controller
{
    private function applyUserNameOrder($query)
    {
        if (Schema::hasColumn('users', 'name')) return $query->orderBy('name');

        if (Schema::hasColumn('users', 'first_name')) {
            $query->orderBy('first_name');
            if (Schema::hasColumn('users', 'last_name')) $query->orderBy('last_name');
            return $query;
        }

        if (Schema::hasColumn('users', 'full_name')) return $query->orderBy('full_name');
        if (Schema::hasColumn('users', 'username')) return $query->orderBy('username');

        return $query->orderBy('email');
    }

    // إذا كان عندك methods أخرى في هذا الكنترولر، ابعتها وأنا أدمجها
}
