<?php

namespace App\Models;

class ProductionWorkAssignment extends Model
{
    protected $fillable = [
        'order_item_id',
        'tailor_id',
        'quantity',
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
        return $this->belongsTo(ProductionOrderItem::class);
    }
    
    public function materialDeliveries()
    {
        return $this->hasMany(MaterialDelivery::class);
    }
}
