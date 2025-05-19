<?php

namespace App\Models;

class WorkAssignment extends Model
{
    protected $fillable = [
        'order_item_id', 'tailor_id', 'quantity_assigned',
        'assigned_at', 'status', 'notes',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function tailor()
    {
        return $this->belongsTo(Tailor::class);
    }

    public function workReturns()
    {
        return $this->hasMany(WorkReturn::class);
    }

    public function qualityChecks()
    {
        return $this->hasMany(WorkQualityCheck::class);
    }

    public function progressLogs()
    {
        return $this->hasMany(WorkProgressLog::class);
    }

    public function payments()
    {
        return $this->hasMany(TailorPayment::class);
    }
}
