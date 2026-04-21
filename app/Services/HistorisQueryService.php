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
        
        Log::info('🔍 [HISTORIS] getChartData START', [
            'dari' => $dari,
            'sampai' => $sampai,
            'metrik' => $metrik,
            'golongans' => $golongans,
            'original_mode' => $originalMode,
            'is_mixed_era' => $isMixed,
        ]);

        // Auto-switch bulanan → triwulan jika mixed era
        if ($isMixed && $mode === 'bulanan') {
            Log::info('⚠️ Auto-switch: bulanan → triwulan karena mixed era');
            $mode = 'triwulan';
        }

        // ── QUERY DATA ─────────────────────────────────────────────────────
        $raw = FactHistoris::whereBetween('tahun', [$dari, $sampai])
            ->where('metrik_kode', strtolower(trim($metrik)))
            ->whereIn('golongan_kode', $golongans)
            ->where('is_akumulasi', 0)
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->get();

        Log::info('📊 [HISTORIS] Query result', [
            'rows_found' => $raw->count(),
            'unique_golongan' => $raw->pluck('golongan_kode')->unique()->values(),
            'unique_tahun' => $raw->pluck('tahun')->unique()->sort()->values(),
            'sample_row' => $raw->first()?->toArray(),
        ]);

        if ($raw->isEmpty()) {
            Log::warning('⚠️ [HISTORIS] Tidak ada data dari database', [
                'filter' => [
                    'tahun' => [$dari, $sampai],
                    'metrik' => $metrik,
                    'golongans' => $golongans,
                ]
            ]);
            return $this->emptyResponse($originalMode, $metrik, $dari, $sampai, $golongans);
        }

        // ── BUILD TREE STRUCTURE ───────────────────────────────────────────
        $tree = [];
        foreach ($raw as $r) {
            $tree[$r->golongan_kode][$r->tahun][$r->bulan] = [
                'nilai'       => (float) $r->nilai,
                'is_triwulan' => (bool) $r->is_triwulan,
                'is_jumlah'   => (bool) $r->is_jumlah_tahunan,
            ];
        }

        Log::info('🌳 [HISTORIS] Tree structure built', [
            'golongan_keys' => array_keys($tree),
            'sample_golongan' => array_key_first($tree),
            'sample_years' => $tree[array_key_first($tree)] ?? [],
        ]);

        // ── BUILD RESULT BY MODE ───────────────────────────────────────────
        $result = match ($mode) {
            'bulanan'  => $this->buildBulanan($tree, $golongans, $dari, $sampai),
            'triwulan' => $this->buildTriwulan($tree, $golongans, $dari, $sampai),
            default    => $this->buildTahunan($tree, $golongans, $dari, $sampai),
        };

        Log::info('📈 [HISTORIS] Result after build', [
            'mode_used' => $mode,
            'labels_count' => count($result['labels'] ?? []),
            'datasets_count' => count($result['datasets'] ?? []),
            'is_empty' => $this->isEmptyResult($result),
        ]);

        // ── FALLBACK LOGIC ─────────────────────────────────────────────────
        if ($originalMode === 'bulanan' && $this->isEmptyResult($result)) {
            Log::info('🔄 Fallback: bulanan empty → coba triwulan');
            $result = $this->buildTriwulan($tree, $golongans, $dari, $sampai);
            
            if (!$this->isEmptyResult($result)) {
                $result['warning'] = "Data untuk {$metrik} ({$dari}-{$sampai}) hanya tersedia dalam format triwulan.";
                $result['mode'] = 'triwulan';
                Log::info('✅ Fallback triwulan berhasil');
            }
        }
        
        if ($this->isEmptyResult($result) && $originalMode !== 'tahunan') {
            Log::info('🔄 Fallback: coba mode tahunan');
            $result = $this->buildTahunan($tree, $golongans, $dari, $sampai);
            
            if (!$this->isEmptyResult($result)) {
                $result['warning'] = "Data detail tidak tersedia, menampilkan agregat tahunan.";
                $result['mode'] = 'tahunan';
                Log::info('✅ Fallback tahunan berhasil');
            }
        }

        // ── FINAL CHECK ────────────────────────────────────────────────────
        if ($this->isEmptyResult($result)) {
            Log::error('❌ [HISTORIS] Semua mode gagal menghasilkan data', [
                'metrik' => $metrik,
                'range' => "$dari-$sampai",
                'debug' => [
                    'rows_found' => $raw->count(),
                    'golongans_checked' => $golongans,
                    'available_months' => $this->getAvailableMonths($tree),
                ]
            ]);
            return $this->emptyResponse($originalMode, $metrik, $dari, $sampai, $golongans, [
                'rows_found' => $raw->count(),
                'available_months' => $this->getAvailableMonths($tree),
            ]);
        }

        Log::info('✅ [HISTORIS] getChartData SUCCESS', [
            'mode' => $result['mode'],
            'labels' => count($result['labels']),
            'datasets' => count($result['datasets']),
        ]);

        return $result;
    }

    /**
     * Helper: Return empty response dengan debug info
     */
    private function emptyResponse(string $mode, string $metrik, int $dari, int $sampai, array $golongans, array $debug = []): array
    {
        return [
            'labels' => [],
            'datasets' => [],
            'mode' => $mode,
            'warning' => "Tidak ada data tersedia untuk {$metrik} ({$dari}-{$sampai}) dalam mode {$mode}.",
            'debug' => array_merge([
                'filter_applied' => [
                    'tahun' => [$dari, $sampai],
                    'metrik' => $metrik,
                    'golongans' => $golongans,
                ],
            ], $debug),
        ];
    }

    /**
     * Cek apakah result chart kosong
     */
    private function isEmptyResult(array $result): bool
    {
        if (empty($result['datasets'])) {
            Log::debug('🔍 isEmptyResult: datasets array kosong');
            return true;
        }
        
        foreach ($result['datasets'] as $idx => $dataset) {
            $nonNull = array_filter($dataset['data'] ?? [], fn($v) => $v !== null);
            
            Log::debug('🔍 isEmptyResult: check dataset', [
                'index' => $idx,
                'golongan' => $dataset['golongan'] ?? $dataset['label'] ?? 'unknown',
                'total_points' => count($dataset['data'] ?? []),
                'non_null_count' => count($nonNull),
                'first_10_values' => array_slice($dataset['data'] ?? [], 0, 10),
            ]);
            
            if (count($nonNull) > 0) {
                return false;
            }
        }
        Log::debug('🔍 isEmptyResult: ALL datasets kosong');
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
                    $months["{$golongan}[{$year}]"] = $available;
                }
            }
        }
        return array_slice($months, 0, 10, true);
    }

    public function getAvailableMetrik(int $dari, int $sampai): array
    {
        $kodes = FactHistoris::whereBetween('tahun', [$dari, $sampai])
            ->where('is_akumulasi', 0)
            ->where('is_jumlah_tahunan', 0)
            ->distinct()
            ->pluck('metrik_kode')
            ->toArray();

        if (empty($kodes)) {
            Log::warning('⚠️ getAvailableMetrik: fallback ke DimPeriode', ['range' => "$dari-$sampai"]);
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
        $granularitas = DimPeriode::whereBetween('tahun', [$dari, $sampai])
            ->pluck('granularitas')
            ->unique()
            ->values()
            ->toArray();
            
        Log::debug('🔍 isMixed check', [
            'range' => "$dari-$sampai",
            'unique_granularitas' => $granularitas,
            'is_mixed' => count($granularitas) > 1,
        ]);
        
        return count($granularitas) > 1;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // BUILD MODES
    // ──────────────────────────────────────────────────────────────────────────

    private function buildTahunan(array $tree, array $golongans, int $dari, int $sampai): array
    {
        Log::debug('🔧 buildTahunan called', ['dari' => $dari, 'sampai' => $sampai, 'golongans' => $golongans]);
        
        $labels = range($dari, $sampai);
        $datasets = [];

        foreach ($golongans as $kode) {
            $values = [];
            foreach ($labels as $tahun) {
                $bulanData = $tree[$kode][$tahun] ?? [];
                $nilai = $this->resolveNilaiTahunan($bulanData);
                $values[] = $nilai;
                
                if ($nilai !== null) {
                    Log::debug("  ✓ {$kode}[{$tahun}] = {$nilai}");
                }
            }
            $datasets[] = $this->makeDataset($kode, $values);
        }

        return [
            'labels'   => array_map('strval', $labels),
            'datasets' => $datasets,
            'mode'     => 'tahunan',
        ];
    }

    private function buildTriwulan(array $tree, array $golongans, int $dari, int $sampai): array
    {
        Log::debug('🔧 buildTriwulan called', [
            'dari' => $dari, 
            'sampai' => $sampai, 
            'golongans' => $golongans,
            'tree_has_data' => !empty($tree),
        ]);
        
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
                        $val = $bulanData[$representBulan]['nilai'] ?? null;
                        $values[] = $val;
                        Log::debug("  TW-mode: {$kode}[{$t}-TW{$tw}] = {$val} (direct)");
                    } else {
                        $val = $this->sumBulananToTw($bulanData, $tw);
                        $values[] = $val;
                        Log::debug("  Bulanan→TW: {$kode}[{$t}-TW{$tw}] = {$val} (sum)");
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

    private function buildBulanan(array $tree, array $golongans, int $dari, int $sampai): array
    {
        Log::debug('🔧 buildBulanan called', ['dari' => $dari, 'sampai' => $sampai]);
        
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
                $isTriwulanOnly = $this->hasTriwulan($bulanData) && !$this->hasBulanan($bulanData);
                
                for ($b = 1; $b <= 12; $b++) {
                    $row = $bulanData[$b] ?? null;
                    
                    if ($isTriwulanOnly) {
                        if (in_array($b, self::TW_BULAN) && $row && !$row['is_jumlah']) {
                            $values[] = $row['nilai'];
                        } else {
                            $values[] = null;
                        }
                    } else {
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

    private function hasTriwulan(array $bulanData): bool
    {
        foreach ($bulanData as $row) {
            if (!empty($row['is_triwulan'])) return true;
        }
        return false;
    }

    private function hasBulanan(array $bulanData): bool
    {
        $count = count(array_filter($bulanData, fn($r) => !$r['is_jumlah']));
        return $count >= 6;
    }

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
        foreach ($bulanData as $row) {
            if (!empty($row['is_jumlah'])) return $row['nilai'];
        }
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
        
        Log::debug('🎨 makeDataset', [
            'golongan' => $kode,
            'nama' => $nama,
            'color' => $color,
            'values_count' => count($values),
            'non_null' => count(array_filter($values, fn($v) => $v !== null)),
        ]);
        
        return [
            'label'           => $nama,
            'golongan'        => $kode,
            'data'            => $values,
            'borderColor'     => $color,
            'backgroundColor' => $color,
        ];
    }
}