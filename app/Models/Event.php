<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'start',
        'end',
        'session_id'
    ];
    public function scopeVisibleTo($q, User $user)
    {
        if ($user->isAdmin() || $user->isFinance()) return $q;

        $roles = method_exists($user, 'roleSlugs') ? $user->roleSlugs() : [];

        return $q->where(function ($w) use ($user, $roles) {
            $w->where('audience_type', 'all')
                ->orWhere(function ($x) use ($roles) {
                    $x->where('audience_type', 'roles');
                    foreach ($roles as $r) {
                        $x->orWhereJsonContains('audience_roles', $r);
                    }
                })
                ->orWhere(function ($x) use ($user) {
                    $x->where('audience_type', 'users')
                        ->whereJsonContains('audience_users', (int)$user->id);
                });
        });
    }
}
