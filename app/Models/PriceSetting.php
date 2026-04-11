<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceSetting extends Model
{
    protected $fillable = [
        'mutu_beton',
        'harga_per_m3',
        'is_active',
    ];

    protected $casts = [
        'harga_per_m3' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}