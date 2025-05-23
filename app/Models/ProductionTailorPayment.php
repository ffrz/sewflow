<?php

namespace App\Models;

class ProductionTailorPayment extends Model
{
    protected $fillable = [
        'work_assignment_id',
        'amount',
        'payment_date',
        'notes',
    ];

    public function work_assignment()
    {
        return $this->belongsTo(WorkAssignment::class, 'work_assignment_id');
    }
}
