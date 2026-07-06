<?php

namespace App\Services;

use App\Repositories\RtFactBabinRepository;
use Illuminate\Support\Collection;

class RtFactBabinService extends BaseService
{
     public function __construct(RtFactBabinRepository $repository)
    {
        parent::__construct($repository);
    }
    // ── 1. CHART BULANAN: Target vs Realisasi per area ───────────────────────
    // Dipakai untuk chart bar/line bulanan dalam satu tahun
    public function getChartBulanan(int $tahun, string $area = 'interkoneksi', string $metrik = 'kwh'): array
    {
        $data = $this->repository->getModel()->newQuery()
            ->where('tahun', $tahun)
            ->where('area', $area)
            ->where('is_akumulasi', 0)
            ->whereIn('tipe', ['target', 'realisasi'])
            ->get();

        $target    = [];
        $realisasi = [];
        $capaian   = []; // % realisasi vs target

        for ($m = 1; $m <= 12; $m++) {
            $rows   = $data->where('bulan', $m);
            $tVal   = $this->getNilai($rows->firstWhere('tipe', 'target'), $metrik);
            $rVal   = $this->getNilai($rows->firstWhere('tipe', 'realisasi'), $metrik);

            $target[]    = $tVal;
            $realisasi[] = $rVal;
            $capaian[]   = ($tVal && $tVal > 0) ? round(($rVal / $tVal) * 100, 2) : null;
        }

        return [
            'target'    => $target,
            'realisasi' => $realisasi,
            'capaian'   => $capaian, // % pencapaian target
        ];
    }

    // ── 2. CHART AKUMULASI: Progress YTD target vs realisasi ─────────────────
    public function getChartAkumulasi(int $tahun, string $area = 'interkoneksi', string $metrik = 'kwh'): array
    {
        $data = $this->repository->getModel()->newQuery()
            ->where('tahun', $tahun)
            ->where('area', $area)
            ->where('is_akumulasi', 1)
            ->whereIn('tipe', ['target', 'realisasi'])
            ->get();

        $target    = [];
        $realisasi = [];
        $gap       = []; // selisih akumulasi realisasi vs target
        $capaian   = []; // % pencapaian akumulasi

        for ($m = 1; $m <= 12; $m++) {
            $rows   = $data->where('bulan', $m);
            $tVal   = $this->getNilai($rows->firstWhere('tipe', 'target'), $metrik);
            $rVal   = $this->getNilai($rows->firstWhere('tipe', 'realisasi'), $metrik);

            $target[]    = $tVal;
            $realisasi[] = $rVal;
            $gap[]       = ($tVal !== null && $rVal !== null) ? round($rVal - $tVal, 2) : null;
            $capaian[]   = ($tVal && $tVal > 0 && $rVal !== null)
                ? round(($rVal / $tVal) * 100, 2)
                : null;
        }

        return [
            'target'    => $target,
            'realisasi' => $realisasi,
            'gap'       => $gap,
            'capaian'   => $capaian,
        ];
    }

    // ── 3. CHART KEDUA AREA: Interkoneksi vs Sembulang dalam satu chart ──────
    public function getChartPerArea(int $tahun, string $metrik = 'kwh', string $tipe = 'realisasi'): array
    {
        $data = $this->repository->getModel()->newQuery()
            ->where('tahun', $tahun)
            ->where('is_akumulasi', 0)
            ->where('tipe', $tipe)
            ->get();

        $interkoneksi = [];
        $sembulang    = [];
        $total        = [];

        for ($m = 1; $m <= 12; $m++) {
            $rows = $data->where('bulan', $m);
            $iVal = $this->getNilai($rows->firstWhere('area', 'interkoneksi'), $metrik);
            $sVal = $this->getNilai($rows->firstWhere('area', 'sembulang'), $metrik);

            $interkoneksi[] = $iVal;
            $sembulang[]    = $sVal;
            $total[]        = ($iVal !== null && $sVal !== null) ? round($iVal + $sVal, 2) : ($iVal ?? $sVal);
        }

        return [
            'interkoneksi' => $interkoneksi,
            'sembulang'    => $sembulang,
            'total'        => $total,
        ];
    }

    // ── 4. GROWTH YoY: Pertumbuhan tahunan per area ──────────────────────────
    // Bandingkan realisasi tahun ini vs tahun lalu (bulan per bulan)
    public function getGrowthYoY(int $tahun, string $area = 'interkoneksi', string $metrik = 'kwh'): array
    {
        $current  = $this->getMonthlyRealisasi($tahun,     $area, $metrik);
        $previous = $this->getMonthlyRealisasi($tahun - 1, $area, $metrik);

        $growth = [];
        for ($i = 0; $i < 12; $i++) {
            $cur  = $current[$i];
            $prev = $previous[$i];
            $growth[] = ($prev !== null && $prev > 0 && $cur !== null)
                ? round((($cur - $prev) / $prev) * 100, 2)
                : null;
        }

        return [
            'tahun_ini'     => $current,
            'tahun_lalu'    => $previous,
            'growth_pct'    => $growth,
            'tahun'         => $tahun,
            'tahun_sebelum' => $tahun - 1,
        ];
    }

    // ── 5. TREND HISTORIS: Realisasi tahunan dari tahun awal s.d. sekarang ───
    // Cocok untuk chart line panjang menampilkan tren multi-tahun
    public function getTrendHistoris(string $area = 'interkoneksi', string $metrik = 'kwh'): array
    {
        $data = $this->repository->getModel()->newQuery()
            ->where('area', $area)
            ->where('tipe', 'realisasi')
            ->where('is_akumulasi', 0)
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->get();

        $grouped = $data->groupBy('tahun');
        $labels  = [];
        $values  = [];

        foreach ($grouped as $tahun => $rows) {
            // Total tahunan (sum semua bulan)
            $sum = $rows->sum(fn($r) => $this->getNilai($r, $metrik) ?? 0);
            $labels[] = (string) $tahun;
            $values[] = round($sum, 2);
        }

        // Growth antar tahun
        $growthAntar = [];
        for ($i = 0; $i < count($values); $i++) {
            if ($i === 0 || $values[$i - 1] == 0) {
                $growthAntar[] = null;
            } else {
                $growthAntar[] = round((($values[$i] - $values[$i - 1]) / $values[$i - 1]) * 100, 2);
            }
        }

        return [
            'labels'       => $labels,
            'values'       => $values,
            'growth_antar' => $growthAntar,
        ];
    }

    // ── 6. SUMMARY DASHBOARD: Snapshot bulan tertentu ────────────────────────
    // Cocok untuk kartu KPI di atas dashboard
    public function getSummary(int $tahun, int $bulan, string $area = 'interkoneksi'): array
    {
        $data = $this->repository->getModel()->newQuery()
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->where('area', $area)
            ->where('is_akumulasi', 0)
            ->get();

        $target    = $data->firstWhere('tipe', 'target');
        $realisasi = $data->firstWhere('tipe', 'realisasi');

        $tKwh = (float) ($target?->nilai_kwh    ?? 0);
        $rKwh = (float) ($realisasi?->nilai_kwh ?? 0);
        $tRp  = (float) ($target?->nilai_rp     ?? 0);
        $rRp  = (float) ($realisasi?->nilai_rp  ?? 0);

        return [
            'area'          => $area,
            'tahun'         => $tahun,
            'bulan'         => $bulan,
            'target_kwh'    => $tKwh,
            'realisasi_kwh' => $rKwh,
            'capaian_kwh'   => $tKwh > 0 ? round(($rKwh / $tKwh) * 100, 2) : null,
            'delta_kwh'     => round($rKwh - $tKwh, 2),
            'target_rp'     => $tRp,
            'realisasi_rp'  => $rRp,
            'capaian_rp'    => $tRp > 0 ? round(($rRp / $tRp) * 100, 2) : null,
            'delta_rp'      => round($rRp - $tRp, 2),
        ];
    }

    // ── 7. ALL CHART DATA: Satu endpoint untuk semua kebutuhan chart ──────────
    // Dipanggil sekali oleh frontend, mengembalikan semua data sekaligus
    public function getAllChartData(int $tahun, string $metrik = 'kwh'): array
    {
        // Fallback hanya untuk data operasional (tahun berjalan)
        // Historis TIDAK kena fallback — selalu tampilkan semua tahun yang ada
        $hasData      = $this->repository->getModel()->newQuery()
            ->where('tahun', $tahun)
            ->where('tipe', 'realisasi')
            ->where('is_akumulasi', 0)
            ->exists();
        $tahunEfektif = $hasData ? $tahun : $tahun - 1;

        return [
            'bulanan' => [
                'interkoneksi' => $this->getChartBulanan($tahunEfektif, 'interkoneksi', $metrik),
                'sembulang'    => $this->getChartBulanan($tahunEfektif, 'sembulang',    $metrik),
            ],
            'akumulasi' => [
                'interkoneksi' => $this->getChartAkumulasi($tahunEfektif, 'interkoneksi', $metrik),
                'sembulang'    => $this->getChartAkumulasi($tahunEfektif, 'sembulang',    $metrik),
            ],
            'per_area'   => $this->getChartPerArea($tahunEfektif, $metrik),
            'growth_yoy' => [
                'interkoneksi' => $this->getGrowthYoY($tahunEfektif, 'interkoneksi', $metrik),
                'sembulang'    => $this->getGrowthYoY($tahunEfektif, 'sembulang',    $metrik),
            ],
            // Historis: ambil SEMUA tahun dari DB, tidak terkena fallback
            'historis_yoy' => [
                'interkoneksi' => $this->getHistorisPerTahun('interkoneksi', $metrik),
                'sembulang'    => $this->getHistorisPerTahun('sembulang',    $metrik),
            ],
            'historis' => [
                'interkoneksi' => $this->getTrendHistoris('interkoneksi', $metrik),
                'sembulang'    => $this->getTrendHistoris('sembulang',    $metrik),
            ],
            'tahun_efektif' => $tahunEfektif,
            'is_fallback'   => !$hasData,
        ];
    }

    // ── 8. HISTORIS PER TAHUN: Data tahunan + growth vs tahun sebelumnya ─────
    public function getHistorisPerTahun(string $area = 'interkoneksi', string $metrik = 'kwh'): array
    {
        $data = $this->repository->getModel()->newQuery()
            ->where('area', $area)
            ->where('tipe', 'realisasi')
            ->where('is_akumulasi', 0)
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->get()
            ->groupBy('tahun');

        $tahunList = $data->keys()->toArray(); // [2018, 2019, ..., 2025]
        $result    = [];

        foreach ($tahunList as $tahun) {
            $monthly   = $this->buildMonthlyValues($data[$tahun], $metrik);
            $prevTahun = $tahun - 1;
            $prevData  = $data[$prevTahun] ?? null;

            $growthBulan   = $this->buildGrowthBulanan($monthly, $prevData, $metrik);
            $totalTahun    = array_sum(array_filter($monthly, fn($v) => $v !== null));
            $prevTotal     = $prevData ? $prevData->sum(fn($r) => $this->getNilai($r, $metrik) ?? 0) : null;
            $growthTahunan = $this->calcGrowthPct($totalTahun, $prevTotal);

            $result[] = [
                'tahun'          => $tahun,
                'bulanan'        => $monthly,
                'total'          => round($totalTahun, 2),
                'growth_bulanan' => $growthBulan,
                'growth_tahunan' => $growthTahunan,
            ];
        }

        return $result;
    }

    // ── PRIVATE HELPERS ───────────────────────────────────────────────────────

    private function getNilai($row, string $metrik): ?float
    {
        if (!$row) {
            return null;
        }
        return match ($metrik) {
            'kwh' => (float) $row->nilai_kwh,
            'rp'  => (float) $row->nilai_rp,
            default => null,
        };
    }

    private function getMonthlyRealisasi(int $tahun, string $area, string $metrik): array
    {
        $data = $this->repository->getModel()->newQuery()
            ->where('tahun', $tahun)
            ->where('area', $area)
            ->where('tipe', 'realisasi')
            ->where('is_akumulasi', 0)
            ->get()
            ->keyBy('bulan');

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $result[] = isset($data[$m]) ? $this->getNilai($data[$m], $metrik) : null;
        }
        return $result;
    }

    /**
     * Bangun array 12 bulan nilai metrik dari koleksi data satu tahun.
     */
    private function buildMonthlyValues(Collection $yearData, string $metrik): array
    {
        $monthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $row       = $yearData->firstWhere('bulan', $m);
            $monthly[] = $row ? $this->getNilai($row, $metrik) : null;
        }
        return $monthly;
    }

    /**
     * Bangun array growth % per bulan dibanding tahun sebelumnya.
     * Mengembalikan array 12 null jika data tahun sebelumnya tidak ada.
     */
    private function buildGrowthBulanan(array $monthly, ?Collection $prevYearData, string $metrik): array
    {
        if (!$prevYearData) {
            return array_fill(0, 12, null);
        }

        $growth = [];
        for ($m = 1; $m <= 12; $m++) {
            $cur     = $monthly[$m - 1];
            $prevRow = $prevYearData->firstWhere('bulan', $m);
            $prevVal = $prevRow ? $this->getNilai($prevRow, $metrik) : null;
            $growth[] = $this->calcGrowthPct($cur, $prevVal);
        }
        return $growth;
    }

    /**
     * Hitung persentase growth antara dua nilai, null jika tidak valid.
     */
    private function calcGrowthPct(?float $cur, ?float $prev): ?float
    {
        if ($cur === null || $prev === null || $prev <= 0) {
            return null;
        }
        return round((($cur - $prev) / $prev) * 100, 2);
    }
}