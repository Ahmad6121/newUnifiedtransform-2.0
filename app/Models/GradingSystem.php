<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradingSystem extends Model
{
    protected $fillable = ['name','class_id','semester_id'];

    public function rules()
    {
        return $this->hasMany(GradeRule::class);
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
}
