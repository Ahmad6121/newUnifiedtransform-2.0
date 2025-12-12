<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentParentInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'father_name',
        'father_phone',
        'mother_name',
        'mother_phone',
        'guardian_name',
        'guardian_phone',
        'parent_address',
        'occupation',
        'parent_user_id',
    ];

    public function student()
    {
        // الطالب هو User في النظام
        return $this->belongsTo(User::class, 'student_id');
    }

    public function parentUser()
    {
        return $this->belongsTo(User::class, 'parent_user_id');
    }
}

