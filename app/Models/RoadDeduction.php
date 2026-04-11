<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoadDeduction extends Model
{
    protected $fillable = [
        'road_estimation_id',
        'jenis_bukaan',
        'panjang_m',
        'lebar_m',
        'jumlah',
        'volume_m3',
    ];
}