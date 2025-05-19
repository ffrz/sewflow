<?php

namespace App\Models;

class WorkQualityCheck extends Model
{
    protected $fillable = [
        'work_assignment_id',
        'result',
        'notes',
        'checked_at',
    ];

    public function workAssignment()
    {
        return $this->belongsTo(WorkAssignment::class);
    }
}
