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
    // 1. Lookup: periode per tahun
    Schema::create('dim_periode', function (Blueprint $table) {
        $table->smallInteger('tahun')->primary();
        $table->enum('granularitas', ['triwulan', 'bulanan']);
        $table->json('metrik_tersedia');
        $table->string('catatan', 255)->nullable();
        $table->timestamps();
    });

    // 2. Lookup: golongan tarif
    Schema::create('dim_golongan', function (Blueprint $table) {
        $table->string('kode', 10)->primary(); // S, R, B, I, P, M, TOTAL
        $table->string('nama', 50);
        $table->json('alias');
        $table->tinyInteger('urutan');
    });

    // 3. Lookup: jenis metrik
    Schema::create('dim_metrik', function (Blueprint $table) {
        $table->string('kode', 30)->primary();
        $table->string('nama', 100);
        $table->string('satuan', 20);
        $table->string('kategori', 30); // penjualan, pendapatan, pelanggan, tarif, pertumbuhan
    });

    // 4. Fakta utama
    Schema::create('fact_historis', function (Blueprint $table) {
        $table->id();
        $table->smallInteger('tahun');
        $table->tinyInteger('bulan');        // 1-12 selalu. TW I=3, TW II=6, TW III=9, TW IV=12
        $table->boolean('is_triwulan')->default(false); // true = data aslinya triwulan
        $table->boolean('is_akumulasi')->default(false);
        $table->boolean('is_jumlah_tahunan')->default(false);
        $table->string('golongan_kode', 10);
        $table->string('metrik_kode', 30);
        $table->decimal('nilai', 25, 6)->default(0);
        $table->enum('sumber', ['import', 'spreadsheet', 'manual'])->default('import');
        $table->timestamps();

        $table->index(['tahun', 'bulan', 'metrik_kode', 'golongan_kode'], 'idx_main');
        $table->unique(
            ['tahun', 'bulan', 'is_triwulan', 'is_akumulasi', 'golongan_kode', 'metrik_kode'],
            'uq_fact'
        );

        $table->foreign('golongan_kode')->references('kode')->on('dim_golongan');
        $table->foreign('metrik_kode')->references('kode')->on('dim_metrik');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historis_tables');
    }
};
