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
       // 2. Migrasi Dimensi Jenis BPP
Schema::create('dim_jenis_bpp', function (Blueprint $table) {
    $table->id('id_jenis_bpp');
    $table->string('nama_bpp', 100); // Contoh: BPP Pembangkit, dll
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dim_jenis_bpp');
    }
};
