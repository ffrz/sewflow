<?php

namespace App\Models;

class ProductionTailorPayment extends Model
{
    protected $fillable = [
        'work_return_id',
        'payroll_id',
        'quantity',
        'cost',
        'amount',
        'datetime',
        'notes',
    ];

    public function work_return()
    {
        return $this->belongsTo(ProductionWorkReturn::class, 'work_return_id');
    }

    public function payroll()
    {
        return $this->belongsTo(ProductionTailorPayroll::class, 'payroll_id');
    }

    public static function booted()
    {
        static::saved(function ($item) {
            static::updateIsPaid($item->work_return_id);
        });

        static::deleted(function ($item) {
            static::updateIsPaid($item->work_return_id);
        });
    }

    public static function updateIsPaid($work_return_id)
    {
        $workReturn = ProductionWorkReturn::with(['work_assignment.order_item'])->find($work_return_id);

        if (!$workReturn) return;

        $totalPaid = static::where('work_return_id', $work_return_id)->sum('amount');
        
        $expectedAmount = $workReturn->quantity * $workReturn->work_assignment->order_item->unit_cost;
        $workReturn->is_paid = $totalPaid >= $expectedAmount;
        $workReturn->save();
    }
}
