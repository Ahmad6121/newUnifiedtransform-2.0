<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id','student_id','issue_date','due_date','return_date','status','notes'
    ];

    protected $casts = [
        'issue_date'=>'date','due_date'=>'date','return_date'=>'date'
    ];

    public function book(){ return $this->belongsTo(Book::class); }
    public function student(){ return $this->belongsTo(User::class, 'student_id'); }
}
