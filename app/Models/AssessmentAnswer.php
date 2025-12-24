<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'attempt_id','question_id','student_id',
        'selected_option_id','answer_text',
        'hotspot_x','hotspot_y',
        'marks_obtained','is_auto_graded'
    ];

    protected $casts = ['is_auto_graded' => 'boolean'];

    public function attempt()
    {
        return $this->belongsTo(AssessmentAttempt::class, 'attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(AssessmentQuestion::class, 'question_id');
    }

    public function selectedOption()
    {
        return $this->belongsTo(AssessmentQuestionOption::class, 'selected_option_id');
    }
}
