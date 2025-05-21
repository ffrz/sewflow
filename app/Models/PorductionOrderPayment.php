<?php

namespace App\Models;

class PorductionOrderPayment extends Model
{
    protected $fillable = [
        'order_id',
        'amount',
        'payment_date',
        'payment_method',
        'notes',
    ];

    public function order()
    {
        return $this->belongsTo(ProductionOrder::class);
    }
}
