<?php

namespace App\Services;

use App\Models\DimGolongan;
use App\Models\DimPeriode;
use App\Models\FactHistoris;
use Illuminate\Support\Facades\Log;

class HistorisQueryService
{
    // TW representatif: TW1=Mar(3), TW2=Jun(6), TW3=Sep(9), TW4=Des(12)
    private const TW_BULAN = [1 => 3, 2 => 6, 3 => 9, 4 => 12];
    private const TW_RANGE = [
        1 => [1, 2, 3],
        2 => [4, 5, 6],
        3 => [7, 8, 9],
        4 => [10, 11, 12],
    ];
    private const COLORS = [
        'S'     => '#ff9800',
        'R'     => '#00ff88',
        'B'     => '#2196f3',
        'I'     => '#e91e63',
        'P'     => '#9c27b0',
        'M'     => '#00bcd4',
        'TOTAL' => '#ffffff',
        'JUMLAH'=> '#aaaaaa',
    ];

    // ──────────────────────────────────────────────────────────────────────────
    // PUBLIC API
    // ──────────────────────────────────────────────────────────────────────────

    public function getChartData(
        int    $dari,
        int    $sampai,
        string $metrik,
        array  $golongans,
        string $mode = 'tahunan'
    ): array {
        $isMixed = $this->isMixed($dari, $sampai);
        if ($isMixed && $mode === 'bulanan') $mode = 'triwulan';

        $raw = FactHistoris::whereBetween('tahun', [$dari, $sampai])
            ->where('metrik_kode', strtolower(trim($metrik)))
            ->whereIn('golongan_kode', $golongans)
            ->where('is_akumulasi', 0)
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->get();

        Log::info('HistorisQueryService.getChartData', [
            'metrik'   => $metrik,
            'golongan' => $golongans,
            'range'    => "$dari-$sampai",
            'mode'     => $mode,
            'rows'     => $raw->count(),
        ]);

        // Susun: golongan → tahun → bulan → data
        $tree = [];
        foreach ($raw as $r) {
            $tree[$r->golongan_kode][$r->tahun][$r->bulan] = [
                'nilai'       => (float) $r->nilai,
                'is_triwulan' => (bool)  $r->is_triwulan,
                'is_jumlah'   => (bool)  $r->is_jumlah_tahunan,
            ];
        }

        return match ($mode) {
            'bulanan'  => $this->buildBulanan($tree, $golongans, $dari, $sampai),
            'triwulan' => $this->buildTriwulan($tree, $golongans, $dari, $sampai),
            default    => $this->buildTahunan($tree, $golongans, $dari, $sampai),
        };
    }

    public function getAvailableMetrik(int $dari, int $sampai): array
    {
        $periodes = DimPeriode::whereBetween('tahun', [$dari, $sampai])->get();
        $all = [];
        foreach ($periodes as $p) {
            $metrik = $p->metrik_tersedia;
            if (is_string($metrik)) $metrik = json_decode($metrik, true);
            if (is_string($metrik)) $metrik = json_decode($metrik, true); // double-encoded fix
            foreach ((array) $metrik as $m) {
                if ($m) $all[$m] = true;
            }
        }
        return array_keys($all);
    }

    public function isMixed(int $dari, int $sampai): bool
    {
        return DimPeriode::whereBetween('tahun', [$dari, $sampai])
            ->pluck('granularitas')
            ->unique()
            ->count() > 1;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // BUILD MODES
    // ──────────────────────────────────────────────────────────────────────────

    /** Satu titik per tahun */
    private function buildTahunan(array $tree, array $golongans, int $dari, int $sampai): array
    {
        $labels = range($dari, $sampai);
        $datasets = [];

        foreach ($golongans as $kode) {
            $values = [];
            foreach ($labels as $tahun) {
                $bulanData = $tree[$kode][$tahun] ?? [];
                $values[]  = $this->resolveNilaiTahunan($bulanData);
            }
            $datasets[] = $this->makeDataset($kode, $values);
        }

        return [
            'labels'   => array_map('strval', $labels),
            'datasets' => $datasets,
            'mode'     => 'tahunan',
        ];
    }

    /** Satu titik per TW per tahun — konversi bulanan→TW jika diperlukan */
    private function buildTriwulan(array $tree, array $golongans, int $dari, int $sampai): array
    {
        $labels = [];
        for ($t = $dari; $t <= $sampai; $t++) {
            foreach (self::TW_BULAN as $tw => $_) {
                $labels[] = "{$t}-TW{$tw}";
            }
        }

        $datasets = [];
        foreach ($golongans as $kode) {
            $values = [];
            for ($t = $dari; $t <= $sampai; $t++) {
                $bulanData   = $tree[$kode][$t] ?? [];
                $isTw        = $this->hasTriwulan($bulanData);

                foreach (self::TW_BULAN as $tw => $representBulan) {
                    if ($isTw) {
                        // Data asli triwulan → ambil langsung
                        $values[] = $bulanData[$representBulan]['nilai'] ?? null;
                    } else {
                        // Bulanan → sum bulan dalam TW (konversi)
                        $values[] = $this->sumBulananToTw($bulanData, $tw);
                    }
                }
            }
            $datasets[] = $this->makeDataset($kode, $values);
        }

        return [
            'labels'   => $labels,
            'datasets' => $datasets,
            'mode'     => 'triwulan',
        ];
    }

    /** Satu titik per bulan per tahun */
    private function buildBulanan(array $tree, array $golongans, int $dari, int $sampai): array
    {
        $bulanNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $labels     = [];
        for ($t = $dari; $t <= $sampai; $t++) {
            foreach ($bulanNames as $bn) $labels[] = "{$t}-{$bn}";
        }

        $datasets = [];
        foreach ($golongans as $kode) {
            $values = [];
            for ($t = $dari; $t <= $sampai; $t++) {
                $bulanData = $tree[$kode][$t] ?? [];
                for ($b = 1; $b <= 12; $b++) {
                    $row      = $bulanData[$b] ?? null;
                    $values[] = ($row && !$row['is_jumlah']) ? $row['nilai'] : null;
                }
            }
            $datasets[] = $this->makeDataset($kode, $values);
        }

        return [
            'labels'   => $labels,
            'datasets' => $datasets,
            'mode'     => 'bulanan',
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Konversi bulanan ke triwulan: jumlahkan bulan dalam rentang TW.
     * Digunakan saat tahun triwulan dibandingkan dengan tahun bulanan.
     */
    public function convertBulananToTriwulan(array $bulananPerBulan): array
    {
        $result = [];
        foreach (self::TW_RANGE as $tw => $bulanList) {
            $sum   = 0;
            $found = false;
            foreach ($bulanList as $b) {
                if (isset($bulananPerBulan[$b]) && $bulananPerBulan[$b] !== null) {
                    $sum  += $bulananPerBulan[$b];
                    $found = true;
                }
            }
            $result["TW{$tw}"] = $found ? $sum : null;
        }
        return $result;
    }

    private function sumBulananToTw(array $bulanData, int $tw): ?float
    {
        $sum   = 0;
        $found = false;
        foreach (self::TW_RANGE[$tw] as $b) {
            $row = $bulanData[$b] ?? null;
            if ($row && !$row['is_jumlah']) {
                $sum  += $row['nilai'];
                $found = true;
            }
        }
        return $found ? $sum : null;
    }

    private function resolveNilaiTahunan(array $bulanData): ?float
    {
        // Cari is_jumlah_tahunan dulu
        foreach ($bulanData as $row) {
            if ($row['is_jumlah']) return $row['nilai'];
        }
        // Fallback: sum semua non-jumlah
        $sum   = 0;
        $found = false;
        foreach ($bulanData as $row) {
            if (!$row['is_jumlah']) { $sum += $row['nilai']; $found = true; }
        }
        return $found ? $sum : null;
    }

    private function hasTriwulan(array $bulanData): bool
    {
        foreach ($bulanData as $row) {
            if ($row['is_triwulan']) return true;
        }
        return false;
    }

    private function makeDataset(string $kode, array $values): array
    {
        $color = self::COLORS[$kode] ?? '#aaaaaa';
        $nama  = DimGolongan::find($kode)?->nama ?? $kode;
        return [
            'label'           => $nama,
            'golongan'        => $kode,
            'data'            => $values,
            'borderColor'     => $color,
            'backgroundColor' => $color,
        ];
    }
}