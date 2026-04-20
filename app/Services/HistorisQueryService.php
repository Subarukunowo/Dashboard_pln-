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
        $originalMode = $mode;
        $isMixed = $this->isMixed($dari, $sampai);
        
        // Auto-switch bulanan → triwulan jika mixed era
        if ($isMixed && $mode === 'bulanan') {
            $mode = 'triwulan';
        }

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
                'is_triwulan' => (bool) $r->is_triwulan,
                'is_jumlah'   => (bool) $r->is_jumlah_tahunan,
            ];
        }

        // 🔄 FALLBACK LOGIC: Jika mode requested tidak menghasilkan data, coba mode lain
        $result = match ($mode) {
            'bulanan'  => $this->buildBulanan($tree, $golongans, $dari, $sampai),
            'triwulan' => $this->buildTriwulan($tree, $golongans, $dari, $sampai),
            default    => $this->buildTahunan($tree, $golongans, $dari, $sampai),
        };

        // Jika result empty dan user requested bulanan, coba triwulan sebagai fallback
        if ($originalMode === 'bulanan' && $this->isEmptyResult($result)) {
            Log::info("Bulanan mode empty, trying triwulan fallback", [
                'metrik' => $metrik,
                'range' => "$dari-$sampai"
            ]);
            
            $result = $this->buildTriwulan($tree, $golongans, $dari, $sampai);
            
            if (!$this->isEmptyResult($result)) {
                $result['warning'] = "Data untuk {$metrik} ({$dari}-{$sampai}) hanya tersedia dalam format triwulan. Mode otomatis: triwulan.";
                $result['mode'] = 'triwulan';
            }
        }
        
        // Jika masih empty, coba tahunan sebagai last resort
        if ($this->isEmptyResult($result) && $originalMode !== 'tahunan') {
            Log::info("Fallback to tahunan mode", [
                'metrik' => $metrik,
                'range' => "$dari-$sampai"
            ]);
            
            $result = $this->buildTahunan($tree, $golongans, $dari, $sampai);
            
            if (!$this->isEmptyResult($result)) {
                $result['warning'] = "Data detail tidak tersedia, menampilkan agregat tahunan.";
                $result['mode'] = 'tahunan';
            }
        }

        // Jika semua fallback gagal, return dengan pesan error yang jelas
        if ($this->isEmptyResult($result)) {
            return [
                'labels' => [],
                'datasets' => [],
                'mode' => $originalMode,
                'warning' => "Tidak ada data tersedia untuk {$metrik} ({$dari}-{$sampai}) dalam mode {$originalMode}.",
                'debug' => [
                    'rows_found' => $raw->count(),
                    'golongans_checked' => $golongans,
                    'available_months' => $this->getAvailableMonths($tree),
                ],
            ];
        }

        return $result;
    }

    /**
     * Cek apakah result chart kosong (semua values null/empty)
     */
    private function isEmptyResult(array $result): bool
    {
        if (empty($result['datasets'])) return true;
        
        foreach ($result['datasets'] as $dataset) {
            if (!empty($dataset['data']) && 
                count(array_filter($dataset['data'], fn($v) => $v !== null)) > 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * Dapatkan bulan-bulan yang tersedia di tree untuk debug
     */
    private function getAvailableMonths(array $tree): array
    {
        $months = [];
        foreach ($tree as $golongan => $years) {
            foreach ($years as $year => $bulanData) {
                $available = array_keys(array_filter($bulanData));
                if (!empty($available)) {
                    $months["{$year}"] = $available;
                }
            }
        }
        return array_slice($months, 0, 5); // Limit untuk log
    }

    public function getAvailableMetrik(int $dari, int $sampai): array
    {
        // ✅ FIX: Ambil metrik yang BENAR-BENAR ada datanya di fact_historis
        $kodes = FactHistoris::whereBetween('tahun', [$dari, $sampai])
            ->where('is_akumulasi', 0)
            ->where('is_jumlah_tahunan', 0)
            ->distinct()
            ->pluck('metrik_kode')
            ->toArray();

        if (empty($kodes)) {
            // Fallback ke DimPeriode jika tidak ada data langsung
            $periodes = DimPeriode::whereBetween('tahun', [$dari, $sampai])->get();
            $all = [];
            foreach ($periodes as $p) {
                $metrik = $p->metrik_tersedia;
                if (is_string($metrik)) $metrik = json_decode($metrik, true);
                if (is_string($metrik)) $metrik = json_decode($metrik, true);
                foreach ((array) $metrik as $m) {
                    if ($m) $all[$m] = true;
                }
            }
            return array_keys($all);
        }

        return $kodes;
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
                $values[] = $this->resolveNilaiTahunan($bulanData);
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
                $bulanData = $tree[$kode][$t] ?? [];
                $isTw = $this->hasTriwulan($bulanData);

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
        $labels = [];
        for ($t = $dari; $t <= $sampai; $t++) {
            foreach ($bulanNames as $bn) $labels[] = "{$t}-{$bn}";
        }

        $datasets = [];
        foreach ($golongans as $kode) {
            $values = [];
            for ($t = $dari; $t <= $sampai; $t++) {
                $bulanData = $tree[$kode][$t] ?? [];
                
                // Cek apakah data ini triwulan-only
                $isTriwulanOnly = $this->hasTriwulan($bulanData) && !$this->hasBulanan($bulanData);
                
                for ($b = 1; $b <= 12; $b++) {
                    $row = $bulanData[$b] ?? null;
                    
                    if ($isTriwulanOnly) {
                        // Jika triwulan-only, hanya tampilkan value di bulan representatif
                        if (in_array($b, self::TW_BULAN) && $row && !$row['is_jumlah']) {
                            $values[] = $row['nilai'];
                        } else {
                            $values[] = null; // Bulan non-triwulan tetap null
                        }
                    } else {
                        // Normal bulanan
                        $values[] = ($row && !$row['is_jumlah']) ? $row['nilai'] : null;
                    }
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
     * Cek apakah bulanData memiliki data triwulan
     */
    private function hasTriwulan(array $bulanData): bool
    {
        foreach ($bulanData as $row) {
            if (!empty($row['is_triwulan'])) return true;
        }
        return false;
    }

    /**
     * Cek apakah bulanData memiliki data bulanan lengkap (minimal 6 bulan)
     */
    private function hasBulanan(array $bulanData): bool
    {
        $count = count(array_filter($bulanData, fn($r) => !$r['is_jumlah']));
        return $count >= 6; // Minimal 6 bulan dianggap data bulanan
    }

    /**
     * Konversi bulanan ke triwulan: jumlahkan bulan dalam rentang TW.
     */
    public function convertBulananToTriwulan(array $bulananPerBulan): array
    {
        $result = [];
        foreach (self::TW_RANGE as $tw => $bulanList) {
            $sum = 0;
            $found = false;
            foreach ($bulanList as $b) {
                if (isset($bulananPerBulan[$b]) && $bulananPerBulan[$b] !== null) {
                    $sum += $bulananPerBulan[$b];
                    $found = true;
                }
            }
            $result["TW{$tw}"] = $found ? $sum : null;
        }
        return $result;
    }

    private function sumBulananToTw(array $bulanData, int $tw): ?float
    {
        $sum = 0;
        $found = false;
        foreach (self::TW_RANGE[$tw] as $b) {
            $row = $bulanData[$b] ?? null;
            if ($row && !$row['is_jumlah']) {
                $sum += $row['nilai'];
                $found = true;
            }
        }
        return $found ? $sum : null;
    }

    private function resolveNilaiTahunan(array $bulanData): ?float
    {
        // Cari is_jumlah_tahunan dulu
        foreach ($bulanData as $row) {
            if (!empty($row['is_jumlah'])) return $row['nilai'];
        }
        // Fallback: sum semua non-jumlah
        $sum = 0;
        $found = false;
        foreach ($bulanData as $row) {
            if (empty($row['is_jumlah'])) { 
                $sum += $row['nilai']; 
                $found = true; 
            }
        }
        return $found ? $sum : null;
    }

    private function makeDataset(string $kode, array $values): array
    {
        $color = self::COLORS[$kode] ?? '#aaaaaa';
        $nama = DimGolongan::find($kode)?->nama ?? $kode;
        return [
            'label'           => $nama,
            'golongan'        => $kode,
            'data'            => $values,
            'borderColor'     => $color,
            'backgroundColor' => $color,
        ];
    }
}