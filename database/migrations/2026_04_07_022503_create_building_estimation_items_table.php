<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('building_estimation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_estimation_id')->constrained()->cascadeOnDelete();

            $table->string('jenis_item'); // pelat|balok|kolom|sloof|lainnya
            $table->string('nama_item')->nullable();

            $table->unsignedInteger('jumlah')->default(1);
            $table->decimal('panjang_m', 10, 3)->default(0);
            $table->decimal('lebar_m', 10, 3)->default(0);
            $table->decimal('tebal_m', 10, 3)->default(0);

            $table->decimal('volume_m3', 14, 4)->default(0);
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('building_estimation_items');
    }
};