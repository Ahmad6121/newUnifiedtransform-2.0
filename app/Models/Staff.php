<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staff';

    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone',
        'salary_type', 'base_salary',
        'join_date', 'status',
        'session_id', 'user_id', 'job_title_id',
    ];

    protected $casts = [
        'join_date' => 'date',
        'base_salary' => 'decimal:2',
    ];

    public function jobTitle()
    {
        return $this->belongsTo(JobTitle::class, 'job_title_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function session()
    {
        return $this->belongsTo(SchoolSession::class, 'session_id');
    }
}
