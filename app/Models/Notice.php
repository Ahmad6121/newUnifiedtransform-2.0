<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    use HasFactory;

    protected $fillable = [
        'notice',
        'session_id',
        'audience_type',   // all|roles|users
        'audience_roles',  // json
        'audience_users',  // json
    ];

    protected $casts = [
        'audience_roles' => 'array',
        'audience_users' => 'array',
    ];

    public function scopeVisibleTo($q, \App\Models\User $user)
    {
        // admin/super_admin يشوفوا الكل دائمًا
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return $q;
        }

        $roles = method_exists($user, 'roleSlugs') ? $user->roleSlugs() : [];

        return $q->where(function ($w) use ($user, $roles) {

            // 1) all
            $w->where('audience_type', 'all')
                ->orWhereNull('audience_type');

            // 2) roles: audience_type=roles AND (contains any role)
            $w->orWhere(function ($x) use ($roles) {
                $x->where('audience_type', 'roles');

                if (empty($roles)) {
                    $x->whereRaw('1=0');
                    return;
                }

                $x->where(function ($rr) use ($roles) {
                    foreach ($roles as $r) {
                        $rr->orWhereJsonContains('audience_roles', $r);
                    }
                });
            });

            // 3) users
            $w->orWhere(function ($x) use ($user) {
                $x->where('audience_type', 'users')
                    ->whereJsonContains('audience_users', (int) $user->id);
            });
        });
    }
}
