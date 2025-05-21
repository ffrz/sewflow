<?php

namespace App\Models;

class ProductionOrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'model',
        'size',
        'quantity',
        'unit_price',
        'total_price',
        'material_source',
        'status',
        'notes',
    ];

    public function order()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function workAssignments()
    {
        return $this->hasMany(WorkAssignment::class);
    }

    public function materialDeliveries()
    {
        return $this->hasMany(MaterialDelivery::class);
    }
}
