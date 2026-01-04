<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryProfile extends Model
{
    protected $fillable = [
        'user_id', 'base_salary', 'pay_cycle', 'effective_from', 'active', 'notes'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
