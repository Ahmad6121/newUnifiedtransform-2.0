<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobTitle extends Model
{
    protected $fillable = ['name'];

    public function staff()
    {
        return $this->hasMany(Staff::class, 'job_title_id');
    }
}
