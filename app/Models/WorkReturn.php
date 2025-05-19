<?php

namespace App\Models;

class WorkReturn extends Model
{
    protected $fillable = [
        'work_assignment_id',
        'quantity_returned',
        'returned_at',
        'reason',
    ];

    public function workAssignment()
    {
        return $this->belongsTo(WorkAssignment::class);
    }
}
