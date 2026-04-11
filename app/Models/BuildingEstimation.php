<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuildingEstimation extends Model
{
    protected $fillable = [
        'user_id',
        'nama_proyek',
        'lokasi_proyek',
        'jumlah_lantai',
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

    public function items()
    {
        return $this->hasMany(BuildingEstimationItem::class);
    }

    public function deductions()
    {
        return $this->hasMany(BuildingEstimationDeduction::class);
    }
}