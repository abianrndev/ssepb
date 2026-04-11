<?php

namespace Database\Seeders;

use App\Models\PriceSetting;
use Illuminate\Database\Seeder;

class PriceSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['mutu_beton' => 'K-175', 'harga_per_m3' => 850000, 'is_active' => true],
            ['mutu_beton' => 'K-200', 'harga_per_m3' => 900000, 'is_active' => true],
            ['mutu_beton' => 'K-225', 'harga_per_m3' => 950000, 'is_active' => true],
            ['mutu_beton' => 'K-250', 'harga_per_m3' => 1000000, 'is_active' => true],
            ['mutu_beton' => 'K-300', 'harga_per_m3' => 1100000, 'is_active' => true],
            ['mutu_beton' => 'K-350', 'harga_per_m3' => 1200000, 'is_active' => true],
        ];

        foreach ($rows as $row) {
            PriceSetting::updateOrCreate(
                ['mutu_beton' => $row['mutu_beton']],
                $row
            );
        }
    }
}