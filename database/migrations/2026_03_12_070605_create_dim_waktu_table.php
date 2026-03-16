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
        // 1. Migrasi Dimensi Waktu
Schema::create('dim_waktu', function (Blueprint $table) {
    $table->id('id_waktu');
    $table->integer('tahun');
    $table->integer('bulan_angka'); // 1 - 12
    $table->string('nama_bulan', 20); // Januari, Februari, dst
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dim_waktu');
    }
};
