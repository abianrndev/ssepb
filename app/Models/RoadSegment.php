<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoadSegment extends Model
{
    protected $fillable = [
        'road_estimation_id',
        'sta_awal',
        'sta_akhir',
        'panjang_m',
        'lebar_m',
        'tebal_m',
        'keterangan',
        'volume_m3',
    ];
}