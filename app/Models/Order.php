<?php

namespace App\Models;

class Order extends Model
{
    protected $fillable = [
        'brand_id',
        'type',
        'status',
        'payment_status',
        'date',
        'due_date',
        'total_quantity',
        'total_price',
        'notes',
    ];

    const Type_Maklon = 'maklon';
    const Type_FullProduction = 'full_production';

    const Types = [
        self::Type_Maklon => 'Maklun',
        self::Type_FullProduction => 'Full Produksi',
    ];

    const Status_Draft = 'draft';
    const Status_Approved = 'approved';
    const Status_InProgress = 'in_progress';
    const Status_Completed = 'completed';
    const Status_Canceled = 'canceled';

    const Statuses = [
        self::Status_Draft => 'Konsep',
        self::Status_Approved => 'Disetujui',
        self::Status_InProgress => 'Dalam Proses',
        self::Status_Completed => 'Selesai',
        self::Status_Canceled => 'Dibatalkan',
    ];

    const PaymentStatus_FullyPaid = 'fully_paid';
    const PaymentStatus_PartiallyPaid = 'partially_paid';
    const PaymentStatus_Unpaid = 'unpaid';
    const PaymentStatus_Refunded = 'refunded';

    const PaymentStatuses = [
        self::PaymentStatus_FullyPaid => 'Lunas',
        self::PaymentStatus_PartiallyPaid => 'Belum Lunas',
        self::PaymentStatus_Unpaid => 'Belum Dibayar',
        self::PaymentStatus_Refunded => 'Dikembalikan',
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
