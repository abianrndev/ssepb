<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quick_cast_estimations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('nama_proyek')->nullable();
            $table->string('lokasi_proyek')->nullable();

            $table->decimal('panjang_m', 10, 2);
            $table->decimal('lebar_m', 10, 2);
            $table->decimal('tebal_cm', 10, 2);
            $table->decimal('tebal_m', 10, 4);

            $table->string('beban_penggunaan'); // ringan/sedang/berat
            $table->string('mutu_rekomendasi'); // K-200, K-250, ...

            $table->decimal('waste_percent', 5, 2)->default(5);

            $table->decimal('volume_kotor', 14, 4);
            $table->decimal('volume_bersih', 14, 4);
            $table->decimal('waste_volume', 14, 4);
            $table->decimal('total_akhir_m3', 14, 4);

            $table->decimal('harga_per_m3', 15, 2)->default(0);
            $table->decimal('estimasi_harga_total', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quick_cast_estimations');
    }
};