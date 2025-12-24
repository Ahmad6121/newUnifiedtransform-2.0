<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = [
        'name','course_id','semester_id','starts','ends',
        'is_online','duration_minutes','max_attempts'
    ];

    protected $casts = [
        'starts' => 'datetime',
        'ends' => 'datetime',
        'is_online' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function rules()
    {
        return $this->hasMany(ExamRule::class);
    }
}
