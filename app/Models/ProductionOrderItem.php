<?php

namespace App\Models;

class ProductionOrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'description',
        'ordered_quantity',
        'assigned_quantity',
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

    public static function booted()
    {
        static::saved(function ($item) {
            self::updateOrderTotals($item->order_id);
        });

        static::deleted(function ($item) {
            self::updateOrderTotals($item->order_id);
        });
    }

    protected static function updateOrderTotals($orderId)
    {
        $items = self::where('order_id', $orderId)->get();

        $totalQuantity = $items->sum('ordered_quantity');
        $totalCost     = $items->sum(function ($item) {
            return $item->unit_cost * $item->ordered_quantity;
        });
        $totalPrice    = $items->sum(function ($item) {
            return $item->unit_price * $item->ordered_quantity;
        });

        ProductionOrder::where('id', $orderId)->update([
            'total_quantity' => $totalQuantity,
            'total_cost'     => $totalCost,
            'total_price'    => $totalPrice,
        ]);
    }

    public function updateCachedQuantities()
    {
        // Hitung ulang dari relasi production_work_assignments
        $assigned = $this->work_assignments()->sum('quantity');

        // Hitung dari relasi production_work_returns via assignments
        $completed = ProductionWorkReturn::whereIn('assignment_id', function ($q) {
            $q->select('id')
                ->from('production_work_assignments')
                ->where('order_item_id', $this->id);
        })->sum('quantity');

        $this->update([
            'assigned_quantity' => $assigned,
            'completed_quantity' => $completed,
        ]);
    }
}
