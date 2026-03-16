<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 4. Migrasi Tabel Fakta BPP
Schema::create('fact_bpp', function (Blueprint $table) {
    $table->id('id_fakta');
    
    // Foreign Keys ke Dimensi
    $table->unsignedBigInteger('id_waktu');
    $table->unsignedBigInteger('id_jenis_bpp');
    $table->unsignedBigInteger('id_versi');
    
    // Nilai Fakta (menggunakan decimal untuk presisi angka)
    $table->decimal('nilai_bpp', 10, 3);

    // Definisi Constraint Foreign Key
    $table->foreign('id_waktu')->references('id_waktu')->on('dim_waktu');
    $table->foreign('id_jenis_bpp')->references('id_jenis_bpp')->on('dim_jenis_bpp');
    $table->foreign('id_versi')->references('id_versi')->on('dim_versi_laporan');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fact_bpp');
    }
};
