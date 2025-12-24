<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamRule extends Model
{
    protected $fillable = ['exam_id','class_id','section_id'];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }
}
