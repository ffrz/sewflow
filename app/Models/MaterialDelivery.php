<?php

namespace App\Models;

class MaterialDelivery extends Model
{
    protected $fillable = [
        'order_item_id',
        'quantity_delivered',
        'delivered_at',
        'notes',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
