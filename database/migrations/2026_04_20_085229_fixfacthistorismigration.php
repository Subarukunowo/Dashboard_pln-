<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Fix anomali data di tabel fact_historis:
 *
 * Bug 1: metrik_kode='KWH_JUAL' (uppercase) → normalkan ke 'kwh_jual'
 *        Asal: 2005, golongan S/R/B/I/P — 60 rows
 *
 * Bug 2: mwh_jual di tahun 2005 → seharusnya kwh_jual
 *        Asal: tahun 2005, golongan R/B/I/M/P — data KWH tapi salah label
 *
 * Bug 3: rupiah 1997-2004 tersimpan dalam ribuan Rp (belum ×1000)
 *        Asal: Transformer lama tidak kalikan ×1000 saat import
 *
 * Bug 4: bulan=0 pada is_jumlah_tahunan → pindahkan ke bulan=12
 *        Asal: Transformer Triwulan menyimpan jumlah tahunan di bulan=0
 *
 * Run: php artisan migrate
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Bug 1: KWH_JUAL → kwh_jual ──────────────────────────
        $fix1 = DB::table('fact_historis')
            ->where('metrik_kode', 'KWH_JUAL')
            ->update([
                'metrik_kode' => 'kwh_jual',
                'satuan'      => 'KWH',
                'updated_at'  => now(),
            ]);
        Log::info("fix_fact_historis: Bug1 KWH_JUAL→kwh_jual: {$fix1} rows");

        // ── Bug 2: mwh_jual 2005 → kwh_jual ─────────────────────
        // Nilai sudah dalam skala KWH, hanya metrik_kode yang salah
        $fix2 = DB::table('fact_historis')
            ->where('tahun', 2005)
            ->where('metrik_kode', 'mwh_jual')
            ->update([
                'metrik_kode' => 'kwh_jual',
                'satuan'      => 'KWH',
                'updated_at'  => now(),
            ]);
        Log::info("fix_fact_historis: Bug2 mwh_jual 2005→kwh_jual: {$fix2} rows");

        // ── Bug 3: rupiah 1997-2004 ribuan → rupiah penuh ────────
        // Original Excel: "PENJUALAN (RIBUAN RUPIAH)"
        // Import tidak kalikan ×1000, jadi 74,832 harus jadi 74,832,000
        $fix3 = DB::table('fact_historis')
            ->whereBetween('tahun', [1997, 2004])
            ->where('metrik_kode', 'rupiah')
            ->update([
                'nilai'      => DB::raw('nilai * 1000'),
                'updated_at' => now(),
            ]);
        Log::info("fix_fact_historis: Bug3 rupiah 1997-2004 ×1000: {$fix3} rows");

        // ── Bug 4: bulan=0 is_jumlah_tahunan → bulan=12 ─────────
        $fix4 = DB::table('fact_historis')
            ->where('bulan', 0)
            ->where('is_jumlah_tahunan', 1)
            ->update([
                'bulan'      => 12,
                'updated_at' => now(),
            ]);
        Log::info("fix_fact_historis: Bug4 bulan=0→12 is_jumlah_tahunan: {$fix4} rows");

        Log::info("fix_fact_historis: SELESAI", [
            'bug1_kwh_jual'    => $fix1,
            'bug2_mwh_2005'    => $fix2,
            'bug3_rupiah_x1000'=> $fix3,
            'bug4_bulan_0'     => $fix4,
        ]);
    }

    public function down(): void
    {
        // Bug 1 & 2: Tidak bisa di-revert tanpa tahu mana yang asli KWH_JUAL vs kwh_jual
        // Bug 3: Bagi kembali dengan 1000
        DB::table('fact_historis')
            ->whereBetween('tahun', [1997, 2004])
            ->where('metrik_kode', 'rupiah')
            ->update(['nilai' => DB::raw('nilai / 1000'), 'updated_at' => now()]);

        // Bug 4: Tidak bisa di-revert (tidak tahu mana yang asli 0 vs 12)
        Log::warning("fix_fact_historis: Rollback hanya Bug3 (rupiah /1000). Bug1,2,4 tidak bisa di-revert.");
    }
};