<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoadEstimation extends Model
{
    protected $fillable = [
        'user_id',
        'nama_proyek',
        'lokasi_proyek',
        'metode_input',
        'jumlah_lajur',
        'lebar_per_lajur_m',
        'bahu_kiri_m',
        'bahu_kanan_m',
        'tebal_beton_m',
        'lebar_total_m',
        'panjang_total_m',
        'mutu_beton',
        'waste_percent',
        'volume_kotor',
        'volume_pengurang',
        'volume_bersih',
        'waste_volume',
        'total_akhir_m3',
        'harga_per_m3',
        'estimasi_harga_total',
    ];

    public function segments()
    {
        return $this->hasMany(RoadSegment::class);
    }

    public function deductions()
    {
        return $this->hasMany(RoadDeduction::class);
    }
}