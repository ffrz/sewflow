<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkProgressLog extends Model
{
    protected $fillable = [
        'work_assignment_id',
        'status',
        'notes',
        'logged_at',
    ];

    public function workAssignment()
    {
        return $this->belongsTo(WorkAssignment::class);
    }
}
