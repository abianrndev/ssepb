<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('road_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('road_estimation_id')->constrained()->cascadeOnDelete();

            $table->string('jenis_bukaan'); // manhole/saluran/utilitas
            $table->decimal('panjang_m', 10, 3);
            $table->decimal('lebar_m', 10, 3);
            $table->unsignedInteger('jumlah')->default(1);

            // asumsi pengurang mengikuti tebal beton utama
            $table->decimal('volume_m3', 14, 4)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('road_deductions');
    }
};