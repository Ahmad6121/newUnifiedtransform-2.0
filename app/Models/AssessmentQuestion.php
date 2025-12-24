<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id','question_type','question_text','image_path',
        'marks','order','correct_text',
        'hotspot_x','hotspot_y','hotspot_radius'
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class, 'assessment_id');
    }

    public function options()
    {
        return $this->hasMany(AssessmentQuestionOption::class, 'question_id');
    }
}
