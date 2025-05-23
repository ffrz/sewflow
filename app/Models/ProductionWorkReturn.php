<?php

namespace App\Models;

class ProductionWorkReturn extends Model
{
    protected $fillable = [
        'assignment_id',
        'quantity',
        'is_paid',
        'datetime',
        'notes',
    ];

    public function work_assignment()
    {
        return $this->belongsTo(ProductionWorkAssignment::class, 'assignment_id');
    }

    public static function booted()
    {
        static::saved(function ($item) {
            self::updateCompletedQuantity($item);
            self::updateReturnedQuantity($item->assignment_id);
            $item->work_assignment->order_item->updateCachedQuantities();
        });

        static::deleted(function ($item) {
            self::updateCompletedQuantity($item);
            self::updateReturnedQuantity($item->assignment_id);
            $item->work_assignment->order_item->updateCachedQuantities();
        });
    }

    protected static function updateCompletedQuantity($return)
    {
        $orderId = $return->work_assignment->order_item->order_id;

        $totalReturned = ProductionWorkReturn::whereHas('work_assignment.order_item', function ($q) use ($orderId) {
            $q->where('order_id', $orderId);
        })->sum('quantity');

        ProductionOrder::where('id', $orderId)
            ->update(['completed_quantity' => $totalReturned]);
    }

    protected static function updateReturnedQuantity($assignmentId)
    {
        $total = self::where('assignment_id', $assignmentId)->sum('quantity');

        ProductionWorkAssignment::where('id', $assignmentId)
            ->update(['returned_quantity' => $total]);
    }
}
