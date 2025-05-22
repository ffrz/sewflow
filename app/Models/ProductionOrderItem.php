<?php

namespace App\Models;

class ProductionOrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'description',
        'ordered_quantity',
        'completed_quantity',
        'unit_cost',
        'total_cost',
        'unit_price',
        'total_price',
        'notes',
    ];

    public function order()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function work_assignments()
    {
        return $this->hasMany(ProductionWorkAssignment::class, 'order_item_id');
    }

    // public function material_deliveries()
    // {
    //     return $this->hasMany(ProdcutionMaterialDelivery::class);
    // }
}
