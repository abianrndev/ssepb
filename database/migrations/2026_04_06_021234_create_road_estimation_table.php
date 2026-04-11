<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('road_estimations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('nama_proyek')->nullable();
            $table->string('lokasi_proyek')->nullable();

            $table->enum('metode_input', ['total', 'segmen'])->default('segmen');

            $table->unsignedInteger('jumlah_lajur')->default(1);
            $table->decimal('lebar_per_lajur_m', 8, 3)->default(0);
            $table->decimal('bahu_kiri_m', 8, 3)->default(0);
            $table->decimal('bahu_kanan_m', 8, 3)->default(0);
            $table->decimal('tebal_beton_m', 8, 3)->default(0);
            $table->decimal('lebar_total_m', 8, 3)->default(0);

            // mode total langsung
            $table->decimal('panjang_total_m', 12, 3)->nullable();

            $table->string('mutu_beton');
            $table->decimal('waste_percent', 5, 2)->default(5);

            // output standar
            $table->decimal('volume_kotor', 14, 4)->default(0);
            $table->decimal('volume_pengurang', 14, 4)->default(0);
            $table->decimal('volume_bersih', 14, 4)->default(0);
            $table->decimal('waste_volume', 14, 4)->default(0);
            $table->decimal('total_akhir_m3', 14, 4)->default(0);

            $table->decimal('harga_per_m3', 15, 2)->default(0);
            $table->decimal('estimasi_harga_total', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('road_estimations');
    }
};