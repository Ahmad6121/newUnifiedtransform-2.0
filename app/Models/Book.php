<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'title','author','isbn',
        'quantity','available_quantity',
        'shelf','publisher','published_year',
        'session_id'
    ];

    public function issues()
    {
        return $this->hasMany(BookIssue::class, 'book_id');
    }

    public function session()
    {
        return $this->belongsTo(SchoolSession::class, 'session_id');
    }
}
