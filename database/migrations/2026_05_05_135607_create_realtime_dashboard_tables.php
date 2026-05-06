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
            // Opsional: Jika dim_kategori ada dan punya kolom 'kode'
            // $table->foreign('golongan_kode')->references('kode')->on('dim_kategori')->onUpdate('cascade');
        });

        // ─────────────────────────────────────────────────────────────
        // 2. FACT: Kinerja Penjualan Bulanan (KWH)
        // ─────────────────────────────────────────────────────────────
        Schema::create('rt_fact_penjualan', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('tahun');
            $table->tinyInteger('bulan');
            $table->boolean('is_triwulan')->default(false);      // ✅ Tambahkan untuk konsistensi
            $table->boolean('is_akumulasi')->default(false);
            $table->string('sub_golongan_kode', 30);
            $table->string('golongan_kode', 10);

            $table->decimal('target_kwh', 25, 6)->default(0);
            $table->decimal('realisasi_kwh', 25, 6)->default(0);
            $table->decimal('realisasi_kwh_lalu', 25, 6)->nullable();
            $table->decimal('pct_pencapaian', 10, 6)->nullable();
            $table->decimal('growth_yoy', 10, 6)->nullable();

            $table->enum('sumber', ['spreadsheet', 'manual'])->default('spreadsheet'); // ✅ Samakan dengan existing
            $table->timestamps();

            $table->unique(['tahun', 'bulan', 'is_triwulan', 'is_akumulasi', 'sub_golongan_kode'], 'rt_penjualan_unique');
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
            $table->string('sub_golongan_kode', 30);
            $table->string('golongan_kode', 10);

            $table->decimal('target_rp', 25, 6)->default(0);
            $table->decimal('realisasi_rp', 25, 6)->default(0);
            $table->decimal('realisasi_rp_lalu', 25, 6)->nullable();
            $table->decimal('pct_pencapaian', 10, 6)->nullable();

            $table->enum('sumber', ['spreadsheet', 'manual'])->default('spreadsheet');
            $table->timestamps();

            $table->unique(['tahun', 'bulan', 'is_triwulan', 'is_akumulasi', 'sub_golongan_kode'], 'rt_pendapatan_unique');
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
            $table->string('sub_golongan_kode', 30);
            $table->string('golongan_kode', 10);

            $table->decimal('target_rpkwh', 25, 6)->default(0);
            $table->decimal('realisasi_rpkwh', 25, 6)->default(0);
            $table->decimal('realisasi_rpkwh_lalu', 25, 6)->nullable();
            $table->decimal('pct_pencapaian', 10, 6)->nullable();

            $table->enum('sumber', ['spreadsheet', 'manual'])->default('spreadsheet');
            $table->timestamps();

            $table->unique(['tahun', 'bulan', 'is_triwulan', 'is_akumulasi', 'sub_golongan_kode'], 'rt_rpkwh_unique');
            $table->index(['tahun', 'bulan', 'golongan_kode']);
        });

        // ─────────────────────────────────────────────────────────────
        // 5. FACT: BPP (Biaya Pokok Produksi) - EDITED: Gunakan FK
        // ─────────────────────────────────────────────────────────────
        Schema::create('rt_fact_bpp', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('tahun');
            $table->tinyInteger('bulan');
            
            // ✅ Gunakan FK ke dim_versi_laporan daripada enum
            $table->unsignedBigInteger('id_versi');
            // ✅ Gunakan FK ke dim_jenis_bpp untuk level/kategori BPP
            $table->unsignedBigInteger('id_jenis_bpp');

            $table->decimal('nilai_rpkwh', 25, 6)->default(0);
            $table->enum('sumber', ['spreadsheet', 'manual'])->default('spreadsheet');
            $table->timestamps();

            $table->unique(['tahun', 'bulan', 'id_versi', 'id_jenis_bpp'], 'rt_bpp_unique');
            $table->index(['tahun', 'bulan']);
            
            // Foreign keys (sesuaikan dengan struktur dim table yang ada)
            $table->foreign('id_versi')->references('id_versi')->on('dim_versi_laporan')->onUpdate('cascade');
            $table->foreign('id_jenis_bpp')->references('id_jenis_bpp')->on('dim_jenis_bpp')->onUpdate('cascade');
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
            $table->string('sub_golongan_kode', 30);
            $table->string('golongan_kode', 10);

            $table->decimal('target_pelanggan', 25, 6)->default(0);
            $table->decimal('realisasi_pelanggan', 25, 6)->default(0);
            $table->decimal('target_kva', 25, 6)->nullable();
            $table->decimal('realisasi_kva', 25, 6)->nullable();
            $table->decimal('pct_pencapaian', 10, 6)->nullable();

            $table->enum('sumber', ['spreadsheet', 'manual'])->default('spreadsheet');
            $table->timestamps();

            $table->unique(['tahun', 'bulan', 'is_triwulan', 'is_akumulasi', 'sub_golongan_kode'], 'rt_pelanggan_unique');
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

            $table->enum('tipe', ['dengan_emin', 'tanpa_emin']);

            $table->decimal('target_pct', 10, 6)->nullable();
            $table->decimal('realisasi_pct', 10, 6)->nullable();
            $table->decimal('target_kwh', 25, 6)->nullable();
            $table->decimal('realisasi_kwh', 25, 6)->nullable();

            $table->enum('sumber', ['spreadsheet', 'manual'])->default('spreadsheet');
            $table->timestamps();

            $table->unique(['tahun', 'bulan', 'is_triwulan', 'is_akumulasi', 'tipe'], 'rt_losses_unique');
            $table->index(['tahun', 'bulan']);
        });

        // ─────────────────────────────────────────────────────────────
        // 8. FACT: Kinerja Tunggakan
        // ─────────────────────────────────────────────────────────────
        Schema::create('rt_fact_tunggakan', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('tahun');
            $table->tinyInteger('bulan');

            $table->decimal('pal', 25, 6)->default(0);
            $table->decimal('tagihan_susulan', 25, 6)->default(0);
            $table->decimal('jumlah_tunggakan', 25, 6)->default(0);
            $table->decimal('pendapatan', 25, 6)->default(0);
            $table->decimal('ratio_pct', 10, 6)->nullable();
            $table->decimal('target_ratio_pct', 10, 6)->nullable();

            $table->enum('sumber', ['spreadsheet', 'manual'])->default('spreadsheet');
            $table->timestamps();

            $table->unique(['tahun', 'bulan'], 'rt_tunggakan_unique');
            $table->index(['tahun', 'bulan']);
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
            $table->string('golongan_kode', 10);

            $table->decimal('target_jam', 10, 2)->nullable();
            $table->decimal('realisasi_jam', 10, 2)->nullable();
            $table->decimal('pct_pencapaian', 10, 6)->nullable();

            $table->enum('sumber', ['spreadsheet', 'manual'])->default('spreadsheet');
            $table->timestamps();

            $table->unique(['tahun', 'bulan', 'is_triwulan', 'is_akumulasi', 'golongan_kode'], 'rt_jam_nyala_unique');
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

            $table->unsignedInteger('jumlah_temuan')->default(0);
            $table->decimal('nilai_tagihan_rp', 25, 6)->default(0);
            $table->decimal('target_rp', 25, 6)->nullable();

            $table->enum('sumber', ['spreadsheet', 'manual'])->default('spreadsheet');
            $table->timestamps();

            $table->unique(['tahun', 'bulan', 'is_triwulan', 'is_akumulasi'], 'rt_p2tl_unique');
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
        // Drop dalam urutan terbalik karena foreign keys
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