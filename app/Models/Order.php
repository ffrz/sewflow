<?php

namespace App\Models;

class Order extends Model
{
    protected $fillable = [
        'brand_id',
        'order_type',
        'order_date',
        'due_date',
        'status',
        'total_quantity',
        'total_price',
        'payment_status',
        'notes',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(BrandPayment::class);
    }
}
