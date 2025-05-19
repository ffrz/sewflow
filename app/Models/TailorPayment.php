<?php

namespace App\Models;

class TailorPayment extends Model
{
    protected $fillable = [
        'work_assignment_id',
        'amount',
        'payment_date',
        'payment_method',
        'notes',
    ];

    public function workAssignment()
    {
        return $this->belongsTo(WorkAssignment::class);
    }
}
