<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan kolom yang hilang di rt_fact_produksi:
     * - tipe_losses: pembeda varian "dengan_emin" vs "tanpa_emin"
     *   (data produksi ternyata berbeda antar 2 sheet Excel, bukan cuma hasil losses-nya)
     * - penjualan_tul3_09: dibutuhkan untuk hitung Losses Distribusi & Jaringan
     * - emin_sistem: hanya ada di sheet "Losses - EMIN", disimpan untuk referensi/audit
     *
     * Unique key lama (tahun, bulan) diganti jadi (tahun, bulan, tipe_losses)
     * karena sekarang 1 bulan bisa punya 2 baris (dengan_emin & tanpa_emin).
     */
    public function up(): void
    {
        Schema::table('rt_fact_produksi', function (Blueprint $table) {
            $table->enum('tipe_losses', ['dengan_emin', 'tanpa_emin'])
                ->default('dengan_emin')
                ->after('bulan');

            $table->decimal('penjualan_tul3_09', 25, 6)
                ->nullable()
                ->after('p_nguan');

            $table->decimal('emin_sistem', 25, 6)
                ->nullable()
                ->after('penjualan_tul3_09');
        });

        // Drop unique key lama (tahun, bulan) kalau ada, ganti ke (tahun, bulan, tipe_losses)
        $indexes = collect(DB::select("SHOW INDEX FROM rt_fact_produksi"))
            ->pluck('Key_name')
            ->unique();

        if ($indexes->contains('rt_fact_produksi_tahun_bulan_unique')) {
            Schema::table('rt_fact_produksi', function (Blueprint $table) {
                $table->dropUnique('rt_fact_produksi_tahun_bulan_unique');
            });
        }

        Schema::table('rt_fact_produksi', function (Blueprint $table) {
            $table->unique(['tahun', 'bulan', 'tipe_losses'], 'rt_fact_produksi_tahun_bulan_tipe_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rt_fact_produksi', function (Blueprint $table) {
            $table->dropUnique('rt_fact_produksi_tahun_bulan_tipe_unique');
        });

        Schema::table('rt_fact_produksi', function (Blueprint $table) {
            $table->dropColumn(['tipe_losses', 'penjualan_tul3_09', 'emin_sistem']);
            $table->unique(['tahun', 'bulan'], 'rt_fact_produksi_tahun_bulan_unique');
        });
    }
};