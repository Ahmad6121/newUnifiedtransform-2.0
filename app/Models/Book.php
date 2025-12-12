<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title','author','isbn','quantity','available_quantity','shelf','publisher','published_year','session_id'
    ];

    public function session(){ return $this->belongsTo(SchoolSession::class,'session_id'); }
    public function issues(){ return $this->hasMany(BookIssue::class); }
}
