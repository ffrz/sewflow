<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'phone', 'address', 'active'
    ];

    public static function activeBrandCount()
    {
        return DB::select(
            'select count(0) as count from brands where active = 1'
        )[0]->count;
    }
}
