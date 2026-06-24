<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─────────────────────────────────────────────────────────────
        // 1. DIM: Sub-Golongan Tarif
        // ─────────────────────────────────────────────────────────────
        Schema::create('rt_dim_sub_golongan', function (Blueprint $table) {
            $table->string('kode', 30)->primary();
            $table->string('nama', 80)->nullable();
            $table->string('golongan_kode', 10);
            $table->unsignedTinyInteger('urutan')->default(0);
            $table->timestamps();

            $table->index('golongan_kode');
        });

        // ─────────────────────────────────────────────────────────────
        // 2. FACT: Kinerja Penjualan Bulanan (KWH)
        // ─────────────────────────────────────────────────────────────
        Schema::create('rt_fact_penjualan', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('tahun');
            $table->tinyInteger('bulan');
            $table->boolean('is_triwulan')->default(false);
            $table->boolean('is_akumulasi')->default(false);
            $table->enum('tipe', ['target', 'realisasi']);
            $table->string('sub_golongan_kode', 30);
            $table->string('golongan_kode', 10);

            $table->decimal('nilai_kwh', 25, 6)->default(0);
            $table->decimal('nilai_kwh_lalu', 25, 6)->nullable();
            $table->decimal('pct_pencapaian', 10, 6)->nullable();
            $table->decimal('growth_yoy', 10, 6)->nullable();

            $table->enum('sumber', ['spreadsheet', 'manual'])->default('spreadsheet');
            $table->timestamps();

            $table->unique(['tahun', 'bulan', 'is_triwulan', 'is_akumulasi', 'tipe', 'sub_golongan_kode'], 'rt_penjualan_unique');
            $table->index(['tahun', 'bulan', 'golongan_kode']);
        });

        // ─────────────────────────────────────────────────────────────
        // 3. FACT: Kinerja Pendapatan Bulanan (Rp)
        // ─────────────────────────────────────────────────────────────
        Schema::create('rt_fact_pendapatan', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('tahun');
            $table->tinyInteger('bulan');
            $table->boolean('is_triwulan')->default(false);
            $table->boolean('is_akumulasi')->default(false);
            $table->enum('tipe', ['target', 'realisasi']);
            $table->string('sub_golongan_kode', 30);
            $table->string('golongan_kode', 10);

            $table->decimal('nilai_rp', 25, 6)->default(0);
            $table->decimal('nilai_rp_lalu', 25, 6)->nullable();
            $table->decimal('pct_pencapaian', 10, 6)->nullable();

            $table->enum('sumber', ['spreadsheet', 'manual'])->default('spreadsheet');
            $table->timestamps();

            $table->unique(['tahun', 'bulan', 'is_triwulan', 'is_akumulasi', 'tipe', 'sub_golongan_kode'], 'rt_pendapatan_unique');
            $table->index(['tahun', 'bulan', 'golongan_kode']);
        });

        // ─────────────────────────────────────────────────────────────
        // 4. FACT: Kinerja RP/KWH
        // ─────────────────────────────────────────────────────────────
        Schema::create('rt_fact_rpkwh', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('tahun');
            $table->tinyInteger('bulan');
            $table->boolean('is_triwulan')->default(false);
            $table->boolean('is_akumulasi')->default(false);
            $table->enum('tipe', ['target', 'realisasi']);
            $table->string('sub_golongan_kode', 30);
            $table->string('golongan_kode', 10);

            $table->decimal('nilai_rpkwh', 25, 6)->default(0);
            $table->decimal('nilai_rpkwh_lalu', 25, 6)->nullable();
            $table->decimal('pct_pencapaian', 10, 6)->nullable();

            $table->enum('sumber', ['spreadsheet', 'manual'])->default('spreadsheet');
            $table->timestamps();

            $table->unique(['tahun', 'bulan', 'is_triwulan', 'is_akumulasi', 'tipe', 'sub_golongan_kode'], 'rt_rpkwh_unique');
            $table->index(['tahun', 'bulan', 'golongan_kode']);
        });

        // ─────────────────────────────────────────────────────────────
        // 5. FACT: BPP (Biaya Pokok Produksi)
        // ─────────────────────────────────────────────────────────────
        Schema::create('rt_fact_bpp', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('tahun');
            $table->tinyInteger('bulan');
            $table->enum('versi', ['konsolidasi', 'internal']);
            $table->enum('jenis_bpp', ['pembangkit', 'transmisi', 'distribusi_tm', 'distribusi_tr']);

            $table->decimal('nilai_rpkwh', 25, 6)->default(0);
            $table->enum('sumber', ['spreadsheet', 'manual'])->default('spreadsheet');
            $table->timestamps();

            $table->unique(['tahun', 'bulan', 'versi', 'jenis_bpp'], 'rt_bpp_unique');
            $table->index(['tahun', 'bulan']);
        });

        // ─────────────────────────────────────────────────────────────
        // 6. FACT: Kinerja Pelanggan
        // ─────────────────────────────────────────────────────────────
        Schema::create('rt_fact_pelanggan', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('tahun');
            $table->tinyInteger('bulan');
            $table->boolean('is_triwulan')->default(false);
            $table->boolean('is_akumulasi')->default(false);
            $table->enum('tipe', ['target', 'realisasi']);
            $table->string('sub_golongan_kode', 30);
            $table->string('golongan_kode', 10);

            $table->decimal('nilai_pelanggan', 25, 6)->default(0);
            $table->decimal('nilai_kva', 25, 6)->nullable();
            $table->decimal('pct_pencapaian', 10, 6)->nullable();

            $table->enum('sumber', ['spreadsheet', 'manual'])->default('spreadsheet');
            $table->timestamps();

            $table->unique(['tahun', 'bulan', 'is_triwulan', 'is_akumulasi', 'tipe', 'sub_golongan_kode'], 'rt_pelanggan_unique');
            $table->index(['tahun', 'bulan', 'golongan_kode']);
        });

        // ─────────────────────────────────────────────────────────────
        // 7. FACT: Losses & EMIN
        // ─────────────────────────────────────────────────────────────
        Schema::create('rt_fact_losses', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('tahun');
            $table->tinyInteger('bulan');
            $table->boolean('is_triwulan')->default(false);
            $table->boolean('is_akumulasi')->default(false);
            $table->enum('tipe_losses', ['dengan_emin', 'tanpa_emin']);
            $table->enum('tipe', ['target', 'realisasi']);

            $table->decimal('nilai_pct', 10, 6)->nullable();
            $table->decimal('nilai_kwh', 25, 6)->nullable();

            $table->enum('sumber', ['spreadsheet', 'manual'])->default('spreadsheet');
            $table->timestamps();

            $table->unique(['tahun', 'bulan', 'is_triwulan', 'is_akumulasi', 'tipe_losses', 'tipe'], 'rt_losses_unique');
            $table->index(['tahun', 'bulan']);
        });

        // ─────────────────────────────────────────────────────────────
        // 8. FACT: Kinerja Tunggakan
        // ─────────────────────────────────────────────────────────────
   Schema::create('rt_fact_tunggakan', function (Blueprint $table) {
    $table->id();
    $table->smallInteger('tahun');
    $table->tinyInteger('bulan');
    $table->decimal('pal_berjalan', 25, 6)->default(0);
    $table->decimal('pal_tunggakan', 25, 6)->default(0);
    $table->decimal('ts_berjalan', 25, 6)->default(0);
    $table->decimal('ts_tunggakan', 25, 6)->default(0);
    $table->decimal('jumlah_tunggakan', 25, 6)->default(0);
    $table->decimal('pendapatan', 25, 6)->default(0);
    $table->decimal('ratio_tunggakan_pct', 10, 6)->default(0);
    $table->enum('sumber', ['spreadsheet', 'manual'])->default('spreadsheet');
    $table->timestamps();
});
        // ─────────────────────────────────────────────────────────────
        // 9. FACT: Jam Nyala
        // ─────────────────────────────────────────────────────────────
        Schema::create('rt_fact_jam_nyala', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('tahun');
            $table->tinyInteger('bulan');
            $table->boolean('is_triwulan')->default(false);
            $table->boolean('is_akumulasi')->default(false);
            $table->enum('tipe', ['target', 'realisasi']);
            $table->string('golongan_kode', 10);

            $table->decimal('nilai_jam', 10, 2)->nullable();
            $table->decimal('pct_pencapaian', 10, 6)->nullable();

            $table->enum('sumber', ['spreadsheet', 'manual'])->default('spreadsheet');
            $table->timestamps();

            $table->unique(['tahun', 'bulan', 'is_triwulan', 'is_akumulasi', 'tipe', 'golongan_kode'], 'rt_jam_nyala_unique');
            $table->index(['tahun', 'bulan']);
        });

        // ─────────────────────────────────────────────────────────────
        // 10. FACT: P2TL
        // ─────────────────────────────────────────────────────────────
        Schema::create('rt_fact_p2tl', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('tahun');
            $table->tinyInteger('bulan');
            $table->boolean('is_triwulan')->default(false);
            $table->boolean('is_akumulasi')->default(false);
            $table->enum('tipe', ['target', 'realisasi']);

            $table->unsignedInteger('jumlah_temuan')->default(0);
            $table->decimal('nilai_tagihan_rp', 25, 6)->default(0);

            $table->enum('sumber', ['spreadsheet', 'manual'])->default('spreadsheet');
            $table->timestamps();

            $table->unique(['tahun', 'bulan', 'is_triwulan', 'is_akumulasi', 'tipe'], 'rt_p2tl_unique');
            $table->index(['tahun', 'bulan']);
        });

        // ─────────────────────────────────────────────────────────────
        // 11. LOG: Import Excel
        // ─────────────────────────────────────────────────────────────
        Schema::create('rt_import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('sheet_name')->nullable();
            $table->smallInteger('tahun');
            $table->tinyInteger('bulan');
            $table->unsignedInteger('rows_inserted')->default(0);
            $table->unsignedInteger('rows_updated')->default(0);
            $table->unsignedInteger('rows_skipped')->default(0);
            $table->text('catatan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tahun', 'bulan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rt_import_logs');
        Schema::dropIfExists('rt_fact_p2tl');
        Schema::dropIfExists('rt_fact_jam_nyala');
        Schema::dropIfExists('rt_fact_tunggakan');
        Schema::dropIfExists('rt_fact_losses');
        Schema::dropIfExists('rt_fact_pelanggan');
        Schema::dropIfExists('rt_fact_bpp');
        Schema::dropIfExists('rt_fact_rpkwh');
        Schema::dropIfExists('rt_fact_pendapatan');
        Schema::dropIfExists('rt_fact_penjualan');
        Schema::dropIfExists('rt_dim_sub_golongan');
    }
};