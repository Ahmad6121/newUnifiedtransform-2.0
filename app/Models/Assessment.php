<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{

    protected $fillable = [
        'session_id','semester_id','class_id','section_id','course_id','teacher_id',
        'title','description','kind','mode',
        'total_marks','passing_marks','weight_percent',
        'start_date','end_date','duration_minutes',
        'is_randomized','attempts_allowed',
        'status','results_published',
        'published_at','closed_at',
    ];

    protected $casts = [
        'is_randomized' => 'boolean',
        'results_published' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'published_at' => 'datetime',
        'closed_at' => 'datetime',
    ];


    public function questions()
    {
        return $this->hasMany(AssessmentQuestion::class)->orderBy('order');
    }

    public function attempts()
    {
        return $this->hasMany(AssessmentAttempt::class);
    }

    public function results()
    {
        return $this->hasMany(AssessmentResult::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }
}
