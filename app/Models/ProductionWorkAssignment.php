<?php

namespace App\Models;

class ProductionWorkAssignment extends Model
{
    protected $fillable = [
        'order_item_id',
        'tailor_id',
        'quantity',
        'returned_quantity',
        'datetime',
        'status',
        'notes',
    ];

    const Status_Assigned = 'assigned';
    const Status_InProgress = 'in_progress';
    const Status_Completed = 'completed'; // selesai tapi belum disetorkan, apakah terpakai???
    const Status_Returned = 'returned';

    const Statuses = [
        self::Status_Assigned => 'Ditugaskan',
        self::Status_InProgress => 'Dikerjakan',
        self::Status_Completed => 'Selesai',
        self::Status_Returned => 'Dikembalikan',
    ];

    public function tailor()
    {
        return $this->belongsTo(Tailor::class);
    }

    public function order_item()
    {
        return $this->belongsTo(ProductionOrderItem::class, 'order_item_id');
    }

    public function returns()
    {
        return $this->hasMany(ProductionWorkReturn::class, 'assignment_id');
    }

    protected static function booted()
    {
        static::saved(function ($assignment) {
            $assignment->order_item?->updateCachedQuantities();
        });

        static::deleted(function ($assignment) {
            $assignment->order_item?->updateCachedQuantities();
        });
    }
}
