<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('road_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('road_estimation_id')->constrained()->cascadeOnDelete();

            $table->string('sta_awal')->nullable();   // contoh 0+000
            $table->string('sta_akhir')->nullable();  // contoh 0+100
            $table->decimal('panjang_m', 12, 3);
            $table->decimal('lebar_m', 8, 3);
            $table->decimal('tebal_m', 8, 3);
            $table->string('keterangan')->nullable();

            $table->decimal('volume_m3', 14, 4)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('road_segments');
    }
};