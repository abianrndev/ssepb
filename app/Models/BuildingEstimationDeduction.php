<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuildingEstimationDeduction extends Model
{
    protected $fillable = [
        'building_estimation_id',
        'jenis_pengurang',
        'jumlah',
        'panjang_m',
        'lebar_m',
        'tebal_m',
        'volume_m3',
        'keterangan',
    ];
}