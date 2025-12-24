<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentResult extends Model
{
    protected $fillable = [
        'assessment_id','student_id',
        'marks_obtained','is_final','graded_by'
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
        'is_final' => 'boolean',
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }
}
