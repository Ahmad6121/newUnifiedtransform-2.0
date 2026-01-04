<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $table = 'payrolls';

    protected $fillable = [
        'employee_type',
        'employee_id',
        'employee_ref',
        'title',
        'amount',
        'payroll_date',
        'notes',
        'created_by',
    ];

    // ✅ Polymorphic relation: employee can be User OR StaffEmployee/Employee...
    public function employee()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
