<?php

namespace App\Models;

class ProductionOrder extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'model',
        'type',
        'status',
        'payment_status',
        'delivery_status',
        'date',
        'due_date',
        'total_quantity',
        'completed_quantity',
        'total_cost',
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
        // self::Status_Draft => 'Konsep',
        // self::Status_Approved => 'Disetujui',
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

    const DeliveryStatus_NotSent = 'not_sent';
    const DeliveryStatus_Sending = 'sending';
    const DeliveryStatus_Delivered = 'delivered';
    const DeliveryStatus_Failed = 'failed';

    const DeliveryStatuses = [
        self::DeliveryStatus_NotSent => 'Belum Dikirim',
        self::DeliveryStatus_Sending => 'Sedang Dikirim',
        self::DeliveryStatus_Delivered => 'Terkirim',
        self::DeliveryStatus_Failed => 'Gagal',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function orderItems()
    {
        return $this->hasMany(ProductionOrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(ProductionOrderPayment::class);
    }
}
