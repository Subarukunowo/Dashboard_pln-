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
       Schema::create('rt_fact_babin', function (Blueprint $table) {
    $table->id();
    $table->smallInteger('tahun');
    $table->tinyInteger('bulan');
    $table->boolean('is_akumulasi')->default(false);
    $table->enum('area', ['interkoneksi', 'sembulang']);
    $table->enum('tipe', ['target', 'realisasi']);
    $table->decimal('nilai_kwh', 25, 6)->default(0);
    $table->decimal('nilai_rp', 25, 6)->default(0);
    $table->enum('sumber', ['spreadsheet', 'manual'])->default('spreadsheet');
    $table->timestamps();

    $table->unique(['tahun', 'bulan', 'is_akumulasi', 'area', 'tipe'], 'rt_babin_unique');
    $table->index(['tahun', 'bulan', 'area']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rt_babin');
    }
};
