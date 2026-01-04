<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryPayment extends Model
{
    protected $fillable = [
        'user_id','amount','salary_month','paid_at','payment_method','reference','paid_by','notes'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'salary_month' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}
