<?php

// app/Models/Staff.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Staff extends Model
{
use HasFactory;

protected $table = 'staff';

protected $fillable = [
'first_name','last_name','email','phone',
'job_title_id','salary_type','base_salary',
'join_date','status','session_id','user_id'
];

protected $casts = [
'base_salary'=>'decimal:2',
'join_date'=>'date',
];

public function session()
{
return $this->belongsTo(SchoolSession::class, 'session_id');
}

public function jobTitle()
{
return $this->belongsTo(JobTitle::class, 'job_title_id');
}

public function getFullNameAttribute(): string
{
return "{$this->first_name} {$this->last_name}";
}
}
