<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuickCastEstimation extends Model
{
    protected $fillable = [
        'user_id',
        'nama_proyek',
        'lokasi_proyek',
        'panjang_m',
        'lebar_m',
        'tebal_cm',
        'tebal_m',
        'beban_penggunaan',
        'mutu_rekomendasi',
        'waste_percent',
        'volume_kotor',
        'volume_bersih',
        'waste_volume',
        'total_akhir_m3',
        'harga_per_m3',
        'estimasi_harga_total',
    ];
}