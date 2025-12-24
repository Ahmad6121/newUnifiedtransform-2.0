<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeRule extends Model
{
    protected $fillable = [
        'grading_system_id','min_percent','max_percent','grade','remark'
    ];

    public function system()
    {
        return $this->belongsTo(GradingSystem::class, 'grading_system_id');
    }
}
