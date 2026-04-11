<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuildingEstimationItem extends Model
{
    protected $fillable = [
        'building_estimation_id',
        'jenis_item',
        'nama_item',
        'jumlah',
        'panjang_m',
        'lebar_m',
        'tebal_m',
        'volume_m3',
        'keterangan',
    ];
}