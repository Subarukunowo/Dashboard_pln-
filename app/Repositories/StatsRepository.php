<?php

namespace App\Repositories;

use App\Models\RtFactBabin;
use App\Models\RtFactBpp;
use App\Models\RtFactHjr;
use App\Models\RtFactJamNyala;
use App\Models\RtFactLosses;
use App\Models\RtFactP2tl;
use App\Models\RtFactPelanggan;
use App\Models\RtFactPendapatan;
use App\Models\RtFactPenjualan;
use App\Models\RtFactProduksi;
use App\Models\RtFactRpkwh;
use App\Models\RtFactTunggakan;
use App\Repositories\Contracts\StatsRepositoryInterface;

class StatsRepository implements StatsRepositoryInterface
{
    // =========================================================================
    // HELPER: fallback tahun/bulan + bulan lalu
    // =========================================================================

    private function resolveYearBulan(string $model, int $tahun, int $bulan, array $extra = []): array
    {
        for ($offset = 0; $offset <= 3; $offset++) {
            $t = $tahun - $offset;
            if ($this->queryExists($model, $t, $bulan, $extra)) {
                return ['tahun' => $t, 'bulan' => $bulan,
                    'is_fallback' => $offset > 0,
                    'fallback_label' => $offset > 0 ? "data dari tahun $t" : null];
            }
            $lb = $model::where('tahun', $t);
            foreach ($extra as $k => $v) {
                $lb->where($k, $v);
            }
            $maxBulan = $lb->max('bulan');
            if ($maxBulan !== null) {
                return ['tahun' => $t, 'bulan' => (int) $maxBulan,
                    'is_fallback' => true,
                    'fallback_label' => "data dari $t bulan $maxBulan"];
            }
        }
        return ['tahun' => $tahun, 'bulan' => $bulan, 'is_fallback' => false, 'fallback_label' => null];
    }

    private function queryExists(string $model, int $tahun, int $bulan, array $extra): bool
    {
        $q = $model::where('tahun', $tahun)->where('bulan', $bulan);
        foreach ($extra as $k => $v) {
            $q->where($k, $v);
        }
        return $q->exists();
    }

    private function prevMonth(int $tahun, int $bulan): array
    {
        return $bulan === 1
            ? ['tahun' => $tahun - 1, 'bulan' => 12]
            : ['tahun' => $tahun,     'bulan' => $bulan - 1];
    }

    private function periodLabel(int $tahun, int $bulan, ?string $fallbackLabel): string
    {
        $base = $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT);
        return $fallbackLabel ? $base . ' ⚠ ' . $fallbackLabel : $base;
    }

    private function selisihPct(float $now, float $prev): ?float
    {
        if ($prev == 0) {
            return null;
        }
        return round((($now - $prev) / abs($prev)) * 100, 2);
    }

    // =========================================================================
    // LOSSES JARINGAN — helper hitung dari rt_fact_produksi
    // ── CATATAN REVISI ───────────────────────────────────────────────────────
    // Sejak refactor arsitektur losses, rt_fact_losses TIDAK LAGI menyimpan
    // baris realisasi (tipe='realisasi') — nilai kWh & % losses sekarang
    // dihitung langsung dari data produksi mentah di rt_fact_produksi.
    // Satu-satunya nilai yang tetap disimpan manual di rt_fact_losses adalah
    // Target % (kategori='jaringan', tipe='target').
    // Rumus di bawah ini SAMA PERSIS dengan RtFactLossesService::buildSource().
    // =========================================================================
    private function hitungLossesJaringanPct(int $tahun, int $bulan, string $tipeLosses = 'dengan_emin'): float
    {
        $p = RtFactProduksi::where('tahun', $tahun)->where('bulan', $bulan)
                ->where('tipe_losses', $tipeLosses)->first();

        if (!$p) {
            return 0.0;
        }

        $prodNetto = (float) ($p->prod_netto ?? 0);
        $psGi      = (float) ($p->ps_gi ?? 0);
        $interkon  = (float) ($p->interkoneksi ?? 0);
        $psDist    = (float) ($p->ps_distribusi ?? 0);
        $kirimLain = (float) ($p->blkg_padang ?? 0) + (float) ($p->sembulang ?? 0)
                   + (float) ($p->buluh ?? 0) + (float) ($p->p_nguan ?? 0);
        $penjualan = (float) ($p->penjualan_tul3_09 ?? 0);

        $lossesJarKwh = $prodNetto - $psGi - $interkon - $psDist - $kirimLain - $penjualan;

        return $prodNetto != 0 ? round(($lossesJarKwh / $prodNetto) * 100, 4) : 0.0;
    }

    private function hitungLossesJaringanAkumPct(int $tahun, int $bulan, string $tipeLosses = 'dengan_emin'): float
    {
        $rows = RtFactProduksi::where('tahun', $tahun)->where('bulan', '<=', $bulan)
                    ->where('tipe_losses', $tipeLosses)->get();

        $akumLosses = 0.0;
        $akumNetto  = 0.0;

        foreach ($rows as $p) {
            $prodNetto = (float) ($p->prod_netto ?? 0);
            $psGi      = (float) ($p->ps_gi ?? 0);
            $interkon  = (float) ($p->interkoneksi ?? 0);
            $psDist    = (float) ($p->ps_distribusi ?? 0);
            $kirimLain = (float) ($p->blkg_padang ?? 0) + (float) ($p->sembulang ?? 0)
                       + (float) ($p->buluh ?? 0) + (float) ($p->p_nguan ?? 0);
            $penjualan = (float) ($p->penjualan_tul3_09 ?? 0);

            $akumLosses += $prodNetto - $psGi - $interkon - $psDist - $kirimLain - $penjualan;
            $akumNetto  += $prodNetto;
        }

        return $akumNetto != 0 ? round(($akumLosses / $akumNetto) * 100, 4) : 0.0;
    }

    // =========================================================================
    // BPP: realisasi pembangkit, target pembangkit, selisih MoM
    // =========================================================================
    public function getBppStats(int $tahun, int $bulan): array
    {
        $r = $this->resolveYearBulan(RtFactBpp::class, $tahun, $bulan, ['versi' => 'konsolidasi']);
        [$t, $b] = [$r['tahun'], $r['bulan']];
        $prev = $this->prevMonth($t, $b);

        $cur  = RtFactBpp::where('tahun', $t)->where('bulan', $b)->where('versi', 'konsolidasi')
                    ->pluck('nilai_rpkwh', 'jenis_bpp');
        $last = RtFactBpp::where('tahun', $prev['tahun'])->where('bulan', $prev['bulan'])->where('versi', 'konsolidasi')
                    ->pluck('nilai_rpkwh', 'jenis_bpp');

        $realisasi = (float) ($cur['pembangkit'] ?? 0);
        $target    = (float) ($cur['transmisi']  ?? 0); // BPP transmisi = acuan "target" display
        $prevReal  = (float) ($last['pembangkit'] ?? 0);

        return [
            'realisasi'      => $realisasi,
            'target'         => $target,
            'mom_selisih'    => $realisasi - $prevReal,
            'mom_pct'        => $this->selisihPct($realisasi, $prevReal),
            'bpp_distribusi_tm' => (float) ($cur['distribusi_tm'] ?? 0),
            'bpp_distribusi_tr' => (float) ($cur['distribusi_tr'] ?? 0),
            'is_fallback'    => $r['is_fallback'],
            'period'         => $this->periodLabel($t, $b, $r['fallback_label']),
        ];
    }

    // =========================================================================
    // LOSSES: jaringan realisasi, target %, selisih MoM
    // ── REVISI ────────────────────────────────────────────────────────────
    // Ketersediaan data & nilai realisasi sekarang dicek/dihitung dari
    // rt_fact_produksi (bukan rt_fact_losses dengan tipe='realisasi', yang
    // sudah tidak pernah diisi lagi sejak refactor). Target tetap diambil
    // dari rt_fact_losses (kategori='jaringan', tipe='target') — satu-
    // satunya nilai losses yang masih disimpan manual di sana.
    // =========================================================================
    public function getLossesStats(int $tahun, int $bulan): array
    {
        $r = $this->resolveYearBulan(RtFactProduksi::class, $tahun, $bulan,
                ['tipe_losses' => 'dengan_emin']);
        [$t, $b] = [$r['tahun'], $r['bulan']];
        $prev = $this->prevMonth($t, $b);

        $jaringan     = $this->hitungLossesJaringanPct($t, $b);
        $akumulasi    = $this->hitungLossesJaringanAkumPct($t, $b);
        $prevJaringan = $this->hitungLossesJaringanPct($prev['tahun'], $prev['bulan']);

        $target = (float) RtFactLosses::where('tahun', $t)->where('bulan', $b)
                    ->where('tipe', 'target')->where('kategori', 'jaringan')
                    ->where('tipe_losses', 'dengan_emin')->where('is_triwulan', 0)->where('is_akumulasi', 0)
                    ->value('nilai_pct') ?? 0;

        return [
            'realisasi'      => $jaringan,
            'target'         => $target,
            'mom_selisih'    => $jaringan - $prevJaringan,
            'mom_pct'        => $this->selisihPct($jaringan, $prevJaringan),
            'losses_akumulasi' => $akumulasi,
            'is_fallback'    => $r['is_fallback'],
            'period'         => $this->periodLabel($t, $b, $r['fallback_label']),
        ];
    }

    // =========================================================================
    // PENJUALAN: realisasi YTD, target tahunan, selisih MoM (akumulasi)
    // =========================================================================
    public function getPenjualanStats(int $tahun, int $bulan): array
    {
        $r = $this->resolveYearBulan(RtFactPenjualan::class, $tahun, $bulan,
                ['tipe' => 'realisasi', 'is_akumulasi' => 1, 'sub_golongan_kode' => 'JUMLAH']);
        [$t, $b] = [$r['tahun'], $r['bulan']];
        $prev = $this->prevMonth($t, $b);

        $realisasi = (float) RtFactPenjualan::where('tahun', $t)->where('bulan', $b)
                        ->where('is_akumulasi', 1)->where('tipe', 'realisasi')->where('sub_golongan_kode', 'JUMLAH')
                        ->value('nilai_kwh') ?? 0;
        $target    = (float) RtFactPenjualan::where('tahun', $t)->where('bulan', 12)
                        ->where('is_akumulasi', 1)->where('tipe', 'target')->where('sub_golongan_kode', 'JUMLAH')
                        ->value('nilai_kwh') ?? 0;
        $prevReal  = (float) RtFactPenjualan::where('tahun', $prev['tahun'])->where('bulan', $prev['bulan'])
                        ->where('is_akumulasi', 1)->where('tipe', 'realisasi')->where('sub_golongan_kode', 'JUMLAH')
                        ->value('nilai_kwh') ?? 0;

        return [
            'realisasi'   => $realisasi,
            'target'      => $target,
            'mom_selisih' => $realisasi - $prevReal,
            'mom_pct'     => $this->selisihPct($realisasi, $prevReal),
            'capai_pct'   => $target > 0 ? round(($realisasi / $target) * 100, 2) : 0,
            'is_fallback' => $r['is_fallback'],
            'period'      => $this->periodLabel($t, $b, $r['fallback_label']),
        ];
    }

    // =========================================================================
    // BABIN: realisasi total, target total, selisih MoM
    // =========================================================================
    public function getBabinStats(int $tahun, int $bulan): array
    {
        $r = $this->resolveYearBulan(RtFactBabin::class, $tahun, $bulan,
                ['tipe' => 'realisasi', 'is_akumulasi' => 0]);
        [$t, $b] = [$r['tahun'], $r['bulan']];
        $prev = $this->prevMonth($t, $b);

        $realisasi = (float) RtFactBabin::where('tahun', $t)->where('bulan', $b)
                        ->where('is_akumulasi', 0)->where('tipe', 'realisasi')->sum('nilai_kwh');
        $target    = (float) RtFactBabin::where('tahun', $t)->where('bulan', $b)
                        ->where('is_akumulasi', 0)->where('tipe', 'target')->sum('nilai_kwh');
        $prevReal  = (float) RtFactBabin::where('tahun', $prev['tahun'])->where('bulan', $prev['bulan'])
                        ->where('is_akumulasi', 0)->where('tipe', 'realisasi')->sum('nilai_kwh');

        $curArea = RtFactBabin::where('tahun', $t)->where('bulan', $b)
                        ->where('is_akumulasi', 0)->where('tipe', 'realisasi')
                        ->pluck('nilai_kwh', 'area');

        return [
            'realisasi'              => $realisasi,
            'target'                 => $target,
            'mom_selisih'            => $realisasi - $prevReal,
            'mom_pct'                => $this->selisihPct($realisasi, $prevReal),
            'capai_pct'              => $target > 0 ? round(($realisasi / $target) * 100, 2) : 0,
            'interkoneksi_realisasi' => (float) ($curArea['interkoneksi'] ?? 0),
            'sembulang_realisasi'    => (float) ($curArea['sembulang'] ?? 0),
            'is_fallback'            => $r['is_fallback'],
            'period'                 => $this->periodLabel($t, $b, $r['fallback_label']),
        ];
    }

    // =========================================================================
    // JAM NYALA: rata-rata jam nyala, target, selisih MoM
    // =========================================================================
    public function getJamNyalaStats(int $tahun, int $bulan): array
    {
        $r = $this->resolveYearBulan(RtFactJamNyala::class, $tahun, $bulan,
                ['tipe' => 'realisasi', 'is_akumulasi' => 0]);
        [$t, $b] = [$r['tahun'], $r['bulan']];
        $prev = $this->prevMonth($t, $b);

        $realisasi = (float) RtFactJamNyala::where('tahun', $t)->where('bulan', $b)
                        ->where('is_akumulasi', 0)->where('tipe', 'realisasi')->avg('nilai_jam') ?? 0;
        $target    = (float) RtFactJamNyala::where('tahun', $t)->where('bulan', $b)
                        ->where('is_akumulasi', 0)->where('tipe', 'target')->avg('nilai_jam') ?? 0;
        $prevReal  = (float) RtFactJamNyala::where('tahun', $prev['tahun'])->where('bulan', $prev['bulan'])
                        ->where('is_akumulasi', 0)->where('tipe', 'realisasi')->avg('nilai_jam') ?? 0;

        $avgPct = (float) RtFactJamNyala::where('tahun', $t)->where('bulan', $b)
                        ->where('is_akumulasi', 0)->where('tipe', 'realisasi')->avg('pct_pencapaian') ?? 0;
        $avgPctFormatted = $avgPct > 1 ? round($avgPct, 2) : round($avgPct * 100, 2);

        return [
            'realisasi'   => round($realisasi, 2),
            'target'      => round($target, 2),
            'mom_selisih' => round($realisasi - $prevReal, 2),
            'mom_pct'     => $this->selisihPct($realisasi, $prevReal),
            'avg_pencapaian' => $avgPctFormatted,
            'is_fallback' => $r['is_fallback'],
            'period'      => $this->periodLabel($t, $b, $r['fallback_label']),
        ];
    }

    // =========================================================================
    // GOLTARIF (HJR): realisasi TR, target TR, selisih MoM TR
    // =========================================================================
    public function getGolTarifStats(int $tahun, int $bulan): array
    {
        $r = $this->resolveYearBulan(RtFactHjr::class, $tahun, $bulan,
                ['tipe' => 'realisasi', 'is_akumulasi' => 1]);
        [$t, $b] = [$r['tahun'], $r['bulan']];
        $prev = $this->prevMonth($t, $b);

        $curReal  = RtFactHjr::where('tahun', $t)->where('bulan', $b)->where('is_akumulasi', 1)->where('tipe', 'realisasi')
                        ->selectRaw('tegangan, AVG(nilai_rpkwh) as avg_rpkwh')->groupBy('tegangan')->pluck('avg_rpkwh', 'tegangan');
        $curTgt   = RtFactHjr::where('tahun', $t)->where('bulan', $b)->where('is_akumulasi', 1)->where('tipe', 'target')
                        ->selectRaw('tegangan, AVG(nilai_rpkwh) as avg_rpkwh')->groupBy('tegangan')->pluck('avg_rpkwh', 'tegangan');
        $lastReal = RtFactHjr::where('tahun', $prev['tahun'])->where('bulan', $prev['bulan'])->where('is_akumulasi', 1)->where('tipe', 'realisasi')
                        ->selectRaw('tegangan, AVG(nilai_rpkwh) as avg_rpkwh')->groupBy('tegangan')->pluck('avg_rpkwh', 'tegangan');

        $realisasi = round((float) ($curReal['TR'] ?? 0), 2);
        $target    = round((float) ($curTgt['TR']  ?? 0), 2);
        $prevReal  = round((float) ($lastReal['TR'] ?? 0), 2);

        return [
            'realisasi'   => $realisasi,
            'target'      => $target,
            'mom_selisih' => $realisasi - $prevReal,
            'mom_pct'     => $this->selisihPct($realisasi, $prevReal),
            'hjr_tr'      => $realisasi,
            'hjr_tm'      => round((float) ($curReal['TM'] ?? 0), 2),
            'hjr_tt'      => round((float) ($curReal['TT'] ?? 0), 2),
            'is_fallback' => $r['is_fallback'],
            'period'      => $this->periodLabel($t, $b, $r['fallback_label']),
        ];
    }

    // =========================================================================
    // PENDAPATAN: realisasi YTD, target tahunan, selisih MoM
    // =========================================================================
    public function getPendapatanStats(int $tahun, int $bulan): array
    {
        $r = $this->resolveYearBulan(RtFactPendapatan::class, $tahun, $bulan,
                ['tipe' => 'realisasi', 'is_akumulasi' => 1, 'sub_golongan_kode' => 'JUMLAH']);
        [$t, $b] = [$r['tahun'], $r['bulan']];
        $prev = $this->prevMonth($t, $b);

        $row  = RtFactPendapatan::where('tahun', $t)->where('bulan', $b)
                    ->where('is_akumulasi', 1)->where('tipe', 'realisasi')->where('sub_golongan_kode', 'JUMLAH')->first();
        $prevRow = RtFactPendapatan::where('tahun', $prev['tahun'])->where('bulan', $prev['bulan'])
                    ->where('is_akumulasi', 1)->where('tipe', 'realisasi')->where('sub_golongan_kode', 'JUMLAH')
                    ->value('nilai_rp') ?? 0;
        $target  = (float) RtFactPendapatan::where('tahun', $t)->where('bulan', 12)
                    ->where('is_akumulasi', 1)->where('tipe', 'target')->where('sub_golongan_kode', 'JUMLAH')
                    ->value('nilai_rp') ?? 0;

        $realisasi = (float) ($row->nilai_rp ?? 0);
        $prevReal  = (float) $prevRow;

        return [
            'realisasi'   => $realisasi,
            'target'      => $target,
            'mom_selisih' => $realisasi - $prevReal,
            'mom_pct'     => $this->selisihPct($realisasi, $prevReal),
            'capai_pct'   => $target > 0 ? round(($realisasi / $target) * 100, 2) : 0,
            'growth_yoy'  => (float) ($row->nilai_rp_lalu ?? 0) > 0
                ? $this->selisihPct($realisasi, (float) ($row->nilai_rp_lalu ?? 0)) : null,
            'is_fallback' => $r['is_fallback'],
            'period'      => $this->periodLabel($t, $b, $r['fallback_label']),
        ];
    }

    // =========================================================================
    // RPKWH: realisasi, target, selisih MoM
    // =========================================================================
    public function getRpkwhStats(int $tahun, int $bulan): array
    {
        $r = $this->resolveYearBulan(RtFactRpkwh::class, $tahun, $bulan,
                ['tipe' => 'realisasi', 'is_akumulasi' => 1, 'sub_golongan_kode' => 'JUMLAH']);
        [$t, $b] = [$r['tahun'], $r['bulan']];
        $prev = $this->prevMonth($t, $b);

        $row     = RtFactRpkwh::where('tahun', $t)->where('bulan', $b)
                    ->where('is_akumulasi', 1)->where('tipe', 'realisasi')->where('sub_golongan_kode', 'JUMLAH')->first();
        $target  = (float) RtFactRpkwh::where('tahun', $t)->where('bulan', $b)
                    ->where('is_akumulasi', 1)->where('tipe', 'target')->where('sub_golongan_kode', 'JUMLAH')
                    ->value('nilai_rpkwh') ?? 0;
        $prevReal = (float) RtFactRpkwh::where('tahun', $prev['tahun'])->where('bulan', $prev['bulan'])
                    ->where('is_akumulasi', 1)->where('tipe', 'realisasi')->where('sub_golongan_kode', 'JUMLAH')
                    ->value('nilai_rpkwh') ?? 0;

        $realisasi = (float) ($row->nilai_rpkwh ?? 0);

        return [
            'realisasi'   => $realisasi,
            'target'      => $target,
            'mom_selisih' => $realisasi - $prevReal,
            'mom_pct'     => $this->selisihPct($realisasi, $prevReal),
            'capai_pct'   => $target > 0 ? round(($realisasi / $target) * 100, 2) : 0,
            'is_fallback' => $r['is_fallback'],
            'period'      => $this->periodLabel($t, $b, $r['fallback_label']),
        ];
    }

    // =========================================================================
    // PELANGGAN: realisasi, target, selisih MoM
    // =========================================================================
    public function getPelangganStats(int $tahun, int $bulan): array
    {
        $r = $this->resolveYearBulan(RtFactPelanggan::class, $tahun, $bulan,
                ['tipe' => 'realisasi', 'is_akumulasi' => 0, 'is_triwulan' => 0, 'sub_golongan_kode' => 'JUMLAH']);
        [$t, $b] = [$r['tahun'], $r['bulan']];
        $prev = $this->prevMonth($t, $b);

        $base = RtFactPelanggan::where('is_akumulasi', 0)->where('is_triwulan', 0)->where('sub_golongan_kode', 'JUMLAH');

        $realisasi = (float) (clone $base)->where('tipe', 'realisasi')->where('tahun', $t)->where('bulan', $b)->value('nilai_pelanggan');
        $target    = (float) (clone $base)->where('tipe', 'target')->where('tahun', $t)->where('bulan', $b)->value('nilai_pelanggan');
        $prevReal  = (float) (clone $base)->where('tipe', 'realisasi')->where('tahun', $prev['tahun'])->where('bulan', $prev['bulan'])->value('nilai_pelanggan');
        $yoy       = (float) (clone $base)->where('tipe', 'realisasi')->where('tahun', $t - 1)->where('bulan', $b)->value('nilai_pelanggan');

        return [
            'realisasi'   => $realisasi,
            'target'      => $target,
            'mom_selisih' => $realisasi - $prevReal,
            'mom_pct'     => $this->selisihPct($realisasi, $prevReal),
            'capai_pct'   => $target > 0 ? round(($realisasi / $target) * 100, 2) : 0,
            'growth_yoy'  => $yoy > 0 ? $this->selisihPct($realisasi, $yoy) : null,
            'is_fallback' => $r['is_fallback'],
            'period'      => $this->periodLabel($t, $b, $r['fallback_label']),
        ];
    }

    // =========================================================================
    // KVA: realisasi, target, selisih MoM
    // =========================================================================
    public function getKvaStats(int $tahun, int $bulan): array
    {
        $r = $this->resolveYearBulan(RtFactPelanggan::class, $tahun, $bulan,
                ['tipe' => 'realisasi', 'is_akumulasi' => 0, 'is_triwulan' => 0, 'sub_golongan_kode' => 'JUMLAH']);
        [$t, $b] = [$r['tahun'], $r['bulan']];
        $prev = $this->prevMonth($t, $b);

        $base = RtFactPelanggan::where('is_akumulasi', 0)->where('is_triwulan', 0)->where('sub_golongan_kode', 'JUMLAH');

        $realisasi = (float) (clone $base)->where('tipe', 'realisasi')->where('tahun', $t)->where('bulan', $b)->value('nilai_kva');
        $target    = (float) (clone $base)->where('tipe', 'target')->where('tahun', $t)->where('bulan', $b)->value('nilai_kva');
        $prevReal  = (float) (clone $base)->where('tipe', 'realisasi')->where('tahun', $prev['tahun'])->where('bulan', $prev['bulan'])->value('nilai_kva');

        return [
            'realisasi'   => $realisasi,
            'target'      => $target,
            'mom_selisih' => $realisasi - $prevReal,
            'mom_pct'     => $this->selisihPct($realisasi, $prevReal),
            'capai_pct'   => $target > 0 ? round(($realisasi / $target) * 100, 2) : 0,
            'is_fallback' => $r['is_fallback'],
            'period'      => $this->periodLabel($t, $b, $r['fallback_label']),
        ];
    }

    // =========================================================================
    // TUNGGAKAN: jumlah tunggakan, pendapatan, selisih MoM
    // =========================================================================
    public function getTunggakanStats(int $tahun, int $bulan): array
    {
        $r = $this->resolveYearBulan(RtFactTunggakan::class, $tahun, $bulan, ['tipe' => 'realisasi']);
        [$t, $b] = [$r['tahun'], $r['bulan']];
        $prev = $this->prevMonth($t, $b);

        $row     = RtFactTunggakan::where('tahun', $t)->where('bulan', $b)->where('tipe', 'realisasi')->first();
        $prevRow = RtFactTunggakan::where('tahun', $prev['tahun'])->where('bulan', $prev['bulan'])->where('tipe', 'realisasi')->first();
        $tgtRow  = RtFactTunggakan::where('tahun', $t)->where('bulan', $b)->where('tipe', 'target')->first();

        $realisasi = (float) ($row->jumlah_tunggakan ?? 0);
        $prevReal  = (float) ($prevRow->jumlah_tunggakan ?? 0);
        $pendapatan = (float) ($row->pendapatan ?? 0);

        return [
            'realisasi'    => $realisasi,
            'target'       => (float) ($tgtRow->jumlah_tunggakan ?? 0),
            'mom_selisih'  => $realisasi - $prevReal,
            'mom_pct'      => $this->selisihPct($realisasi, $prevReal),
            'pendapatan'   => $pendapatan,
            'ratio_pct'    => (float) ($row->ratio_pct ?? 0),
            'target_ratio' => (float) ($tgtRow->ratio_pct ?? 0),
            'is_fallback'  => $r['is_fallback'],
            'period'       => $this->periodLabel($t, $b, $r['fallback_label']),
        ];
    }

    // =========================================================================
    // P2TL: pemeriksaan, temuan, tagihan (YTD), selisih temuan MoM
    // =========================================================================
    public function getP2tlStats(int $tahun, int $bulan): array
    {
        $r = $this->resolveYearBulan(RtFactP2tl::class, $tahun, $bulan,
                ['tipe' => 'realisasi', 'is_akumulasi' => 0]);
        [$t, $b] = [$r['tahun'], $r['bulan']];
        $prev = $this->prevMonth($t, $b);

        $base = fn($yr, $mo) => RtFactP2tl::where('tahun', $yr)->where('bulan', '<=', $mo)
                    ->where('is_akumulasi', 0)->where('tipe', 'realisasi')
                    ->selectRaw('SUM(jumlah_pemeriksaan) as p, SUM(jumlah_temuan) as t, SUM(nilai_tagihan_rp) as rp, SUM(temuan_kwh) as kwh')
                    ->first();

        $ytd     = $base($t, $b);
        $ytdPrev = $base($t, $prev['bulan'] === 12 ? $b : $prev['bulan']); // YTD sampai bulan lalu

        $temuan    = (int) ($ytd->t ?? 0);
        $prevTemuan = (int) ($ytdPrev->t ?? 0);
        $tagihan   = (float) ($ytd->rp ?? 0);

        return [
            'realisasi'         => $temuan,           // temuan = "realisasi" di P2TL
            'target'            => (int) RtFactP2tl::where('tahun', $t)->where('bulan', '<=', $b)
                                        ->where('is_akumulasi', 0)->where('tipe', 'target')->sum('jumlah_temuan'),
            'mom_selisih'       => $temuan - $prevTemuan,
            'mom_pct'           => $this->selisihPct($temuan, $prevTemuan),
            'total_pemeriksaan' => (int) ($ytd->p ?? 0),
            'total_tagihan_rp'  => $tagihan,
            'total_kwh'         => (float) ($ytd->kwh ?? 0),
            'is_fallback'       => $r['is_fallback'],
            'period'            => $this->periodLabel($t, $b, $r['fallback_label']),
        ];
    }
}