<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Routine extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'start',
        'end',
        'weekday',
        'class_id',
        'section_id',
        'course_id',
        'session_id',
    ];

    /**
     * Get the schoolClass.
     */
    public function schoolClass() {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get the section.
     */
    public function section() {
        return $this->belongsTo(Section::class, 'section_id');
    }

    /**
     * Get the course.
     */
//    public function course() {
//        return $this->belongsTo(Course::class, 'course_id');
//    }
    public function course()
    {
        return $this->belongsTo(\App\Models\Course::class, 'course_id');
    }

    public function teacher()
    {
        return $this->belongsTo(\App\Models\User::class, 'teacher_id');
    }
    // app/Models/Routine.php

    public function class()   { return $this->belongsTo(\App\Models\SchoolClass::class, 'class_id'); }


}
