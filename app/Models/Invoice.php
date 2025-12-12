<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id','class_id','session_id','title','amount','status','due_date','notes'
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'due_date'=> 'date',
    ];

    public function student() { return $this->belongsTo(User::class, 'student_id'); }
    public function class()   { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function session() { return $this->belongsTo(SchoolSession::class, 'session_id'); }
    public function payments(){ return $this->hasMany(Payment::class); }

    public function paidTotal(): float {
        return (float) $this->payments()->sum('amount');
    }
}
