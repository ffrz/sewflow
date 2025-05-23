<?php

namespace App\Models;

class ProductionTailorPayroll extends Model
{
    protected $fillable = [
        'tailor_id',
        'period_start',
        'period_end',
        'total_amount',
        'status',
    ];

    public function tailor()
    {
        return $this->belongsTo(Tailor::class);
    }

    public function payments()
    {
        return $this->hasMany(ProductionTailorPayment::class, 'payroll_id');
    }
}
