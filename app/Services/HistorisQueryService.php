<?php

namespace App\Services;

use App\Models\DimGolongan;
use App\Models\DimPeriode;
use App\Models\FactHistoris;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HistorisQueryService
{
    private const TW_BULAN = [1 => 3, 2 => 6, 3 => 9, 4 => 12];
    private const TW_RANGE = [
        1 => [1, 2, 3],
        2 => [4, 5, 6],
        3 => [7, 8, 9],
        4 => [10, 11, 12],
    ];
    private const COLORS = [
        'S'      => '#ff9800',
        'R'      => '#00ff88',
        'B'      => '#2196f3',
        'I'      => '#e91e63',
        'P'      => '#9c27b0',
        'M'      => '#00bcd4',
        'TOTAL'  => '#ffffff',
        'JUMLAH' => '#aaaaaa',
    ];

    // =========================================================
    // PUBLIC API — Chart
    // =========================================================

    public function getChartData(
        int    $dari,
        int    $sampai,
        string $metrik,
        array  $golongans,
        string $mode = 'tahunan'
    ): array {
        $originalMode = $mode;
        $isMixed      = $this->isMixed($dari, $sampai);

        // Mixed era: 1997-2004 triwulan, 2005+ bulanan
        // Kalau user pilih bulanan di range mixed → auto triwulan (tidak ada data bulanan 1997-2004)
        if ($isMixed && $mode === 'bulanan') {
            $mode = 'triwulan';
        }

        $metrikNorm = strtolower(trim($metrik));

        $raw = FactHistoris::whereBetween('tahun', [$dari, $sampai])
            ->whereRaw('LOWER(metrik_kode) = ?', [$metrikNorm])
            ->whereIn('golongan_kode', $golongans)
            ->where('is_akumulasi', 0)
            ->where('bulan', '>', 0)
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->get();

        Log::info('HistorisQueryService.getChartData', [
            'metrik'   => $metrikNorm,
            'golongan' => $golongans,
            'range'    => "$dari-$sampai",
            'mode'     => $mode,
            'rows'     => $raw->count(),
        ]);

        // Build tree: golongan → tahun → bulan → data
        $tree = [];
        foreach ($raw as $r) {
            $tree[$r->golongan_kode][$r->tahun][$r->bulan] = [
                'nilai'       => (float) $r->nilai,
                'is_triwulan' => (bool)  $r->is_triwulan,
                'is_jumlah'   => (bool)  $r->is_jumlah_tahunan,
            ];
        }

        $result = match ($mode) {
            'bulanan'  => $this->buildBulanan($tree, $golongans, $dari, $sampai),
            'triwulan' => $this->buildTriwulan($tree, $golongans, $dari, $sampai),
            default    => $this->buildTahunan($tree, $golongans, $dari, $sampai),
        };

        // Fallback: bulanan kosong → triwulan
        if ($originalMode === 'bulanan' && $this->isEmptyResult($result)) {
            $result = $this->buildTriwulan($tree, $golongans, $dari, $sampai);
            if (!$this->isEmptyResult($result)) {
                $result['warning'] = "Data {$metrik} ({$dari}–{$sampai}) hanya tersedia dalam format triwulan.";
                $result['mode']    = 'triwulan';
            }
        }

        // Fallback: masih kosong → tahunan
        if ($this->isEmptyResult($result) && $originalMode !== 'tahunan') {
            $result = $this->buildTahunan($tree, $golongans, $dari, $sampai);
            if (!$this->isEmptyResult($result)) {
                $result['warning'] = 'Data detail tidak tersedia, menampilkan agregat tahunan.';
                $result['mode']    = 'tahunan';
            }
        }

        if ($this->isEmptyResult($result)) {
            return [
                'labels'   => [],
                'datasets' => [],
                'mode'     => $originalMode,
                'warning'  => "Tidak ada data untuk {$metrik} ({$dari}–{$sampai}) mode {$originalMode}.",
            ];
        }

        // Tambahkan flag mixed agar JS bisa tampilkan warning
        $result['mixed'] = $isMixed && $originalMode !== $result['mode'];
        return $result;
    }

    public function getAvailableMetrik(int $dari, int $sampai): array
    {
        $kodes = FactHistoris::whereBetween('tahun', [$dari, $sampai])
            ->where('is_akumulasi', 0)
            ->where('is_jumlah_tahunan', 0)
            ->where('bulan', '>', 0)
            ->selectRaw('LOWER(metrik_kode) as metrik_kode')
            ->distinct()
            ->pluck('metrik_kode')
            ->toArray();

        if (empty($kodes)) {
            $periodes = DimPeriode::whereBetween('tahun', [$dari, $sampai])->get();
            $all      = [];
            foreach ($periodes as $p) {
                $metrik = $p->metrik_tersedia;
                if (is_string($metrik)) $metrik = json_decode($metrik, true);
                foreach ((array) $metrik as $m) {
                    if ($m) $all[strtolower($m)] = true;
                }
            }
            $kodes = array_keys($all);
        }

        $info   = $this->metrikInfo();
        $result = [];
        foreach (array_unique($kodes) as $kode) {
            if (isset($info[$kode])) {
                $result[] = $info[$kode];
            }
        }

        $order = ['kwh_jual','mwh_jual','rupiah','pelanggan','daya_va','rpkwh','pertumbuhan_yoy'];
        usort($result, fn($a, $b) =>
            (array_search($a['kode'], $order) ?? 99) <=> (array_search($b['kode'], $order) ?? 99)
        );

        return $result;
    }

    public function isMixed(int $dari, int $sampai): bool
    {
        return DimPeriode::whereBetween('tahun', [$dari, $sampai])
            ->pluck('granularitas')
            ->unique()
            ->count() > 1;
    }

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

    // =========================================================
    // AUTO-COMPUTE TRIWULAN
    // =========================================================

    public function saveTriwulanFromBulanan(
        int    $tahun,
        string $golonganKode,
        string $metrikKode,
        string $satuan = null
    ): array {
        $metrikNorm = strtolower(trim($metrikKode));

        $bulanan = FactHistoris::where('tahun', $tahun)
            ->where('golongan_kode', $golonganKode)
            ->whereRaw('LOWER(metrik_kode) = ?', [$metrikNorm])
            ->where('is_triwulan', 0)
            ->where('is_akumulasi', 0)
            ->where('is_jumlah_tahunan', 0)
            ->where('bulan', '>', 0)
            ->pluck('nilai', 'bulan')
            ->toArray();

        $saved = [];

        foreach (self::TW_RANGE as $tw => $bulanList) {
            $representBulan = self::TW_BULAN[$tw];
            $allPresent     = true;
            $sum            = 0;

            foreach ($bulanList as $b) {
                if (!isset($bulanan[$b])) {
                    $allPresent = false;
                    break;
                }
                $sum += (float) $bulanan[$b];
            }

            if (!$allPresent) {
                $saved["tw{$tw}"] = null;
                continue;
            }

            FactHistoris::updateOrCreate(
                [
                    'tahun'             => $tahun,
                    'bulan'             => $representBulan,
                    'golongan_kode'     => $golonganKode,
                    'metrik_kode'       => $metrikNorm,
                    'is_triwulan'       => 1,
                    'is_akumulasi'      => 0,
                    'is_jumlah_tahunan' => 0,
                ],
                [
                    'nilai'      => $sum,
                    'satuan'     => $satuan ?? $this->defaultSatuan($metrikNorm),
                    'sumber'     => 'manual',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $saved["tw{$tw}"] = $sum;
        }

        return $saved;
    }

    public function saveAllTriwulanForTahun(int $tahun): int
    {
        $combinations = FactHistoris::where('tahun', $tahun)
            ->where('is_triwulan', 0)
            ->where('is_akumulasi', 0)
            ->where('is_jumlah_tahunan', 0)
            ->where('bulan', '>', 0)
            ->select('golongan_kode', DB::raw('LOWER(metrik_kode) as metrik_kode'), 'satuan')
            ->distinct()
            ->get();

        $count = 0;
        foreach ($combinations as $combo) {
            $result = $this->saveTriwulanFromBulanan(
                $tahun, $combo->golongan_kode, $combo->metrik_kode, $combo->satuan
            );
            $count += count(array_filter($result));
        }

        return $count;
    }

    // =========================================================
    // BUILD MODES
    // =========================================================

    private function buildTahunan(array $tree, array $golongans, int $dari, int $sampai): array
    {
        $labels   = range($dari, $sampai);
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
                $isTw      = $this->hasTriwulan($bulanData);

                foreach (self::TW_BULAN as $tw => $representBulan) {
                    if ($isTw) {
                        $row      = $bulanData[$representBulan] ?? null;
                        $values[] = ($row && !$row['is_jumlah']) ? $row['nilai'] : null;
                    } else {
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

    /**
     * Mode bulanan — HANYA tampilkan tahun yang punya data bulanan.
     * Tahun triwulan-only di-skip dari label.
     * Fix: X axis format "2005-Jan", "2005-Feb" bukan "2005-TW1"
     */
    private function buildBulanan(array $tree, array $golongans, int $dari, int $sampai): array
    {
        $bulanNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

        // Tentukan tahun mana yang punya data bulanan
        $tahunDenganBulanan = [];
        for ($t = $dari; $t <= $sampai; $t++) {
            foreach ($golongans as $kode) {
                $bulanData = $tree[$kode][$t] ?? [];
                if ($this->hasBulanan($bulanData)) {
                    $tahunDenganBulanan[] = $t;
                    break;
                }
            }
        }

        if (empty($tahunDenganBulanan)) {
            return ['labels' => [], 'datasets' => [], 'mode' => 'bulanan'];
        }

        // Labels format: "2005-Jan", "2005-Feb" ... bukan "TW"
        $labels = [];
        foreach ($tahunDenganBulanan as $t) {
            foreach ($bulanNames as $bn) {
                $labels[] = "{$t}-{$bn}";
            }
        }

        $datasets = [];
        foreach ($golongans as $kode) {
            $values = [];
            foreach ($tahunDenganBulanan as $t) {
                $bulanData = $tree[$kode][$t] ?? [];
                for ($b = 1; $b <= 12; $b++) {
                    $row      = $bulanData[$b] ?? null;
                    $values[] = ($row && !$row['is_jumlah'] && !$row['is_triwulan'])
                        ? $row['nilai']
                        : null;
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

    // =========================================================
    // HELPERS
    // =========================================================

    private function hasTriwulan(array $bulanData): bool
    {
        foreach ($bulanData as $row) {
            if (!empty($row['is_triwulan'])) return true;
        }
        return false;
    }

    private function hasBulanan(array $bulanData): bool
    {
        foreach ($bulanData as $row) {
            if (!$row['is_jumlah'] && !$row['is_triwulan']) return true;
        }
        return false;
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
        foreach ($bulanData as $row) {
            if (!empty($row['is_jumlah'])) return $row['nilai'];
        }
        $sum   = 0;
        $found = false;
        foreach ($bulanData as $row) {
            if (empty($row['is_jumlah'])) {
                $sum  += $row['nilai'];
                $found = true;
            }
        }
        return $found ? $sum : null;
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

    private function isEmptyResult(array $result): bool
    {
        if (empty($result['datasets'])) return true;
        foreach ($result['datasets'] as $ds) {
            if (!empty(array_filter($ds['data'] ?? [], fn($v) => $v !== null))) return false;
        }
        return true;
    }

    private function defaultSatuan(string $metrikKode): string
    {
        return [
            'kwh_jual'        => 'KWH',
            'mwh_jual'        => 'MWH',
            'rupiah'          => 'Rp',
            'pelanggan'       => 'pelanggan',
            'daya_va'         => 'VA',
            'rpkwh'           => 'Rp/kWh',
            'pertumbuhan_yoy' => '%',
        ][$metrikKode] ?? '';
    }

    private function metrikInfo(): array
    {
        return [
            'kwh_jual'        => ['kode' => 'kwh_jual',        'nama' => 'Energi Jual',         'satuan' => 'KWH',    'kategori' => 'energy'],
            'mwh_jual'        => ['kode' => 'mwh_jual',        'nama' => 'Energi Jual (MWH)',    'satuan' => 'MWH',    'kategori' => 'energy'],
            'rupiah'          => ['kode' => 'rupiah',          'nama' => 'Pendapatan Penjualan', 'satuan' => 'Rp',     'kategori' => 'revenue'],
            'pelanggan'       => ['kode' => 'pelanggan',       'nama' => 'Jumlah Pelanggan',     'satuan' => 'org',    'kategori' => 'customer'],
            'daya_va'         => ['kode' => 'daya_va',         'nama' => 'Daya Tersambung',      'satuan' => 'VA',     'kategori' => 'capacity'],
            'rpkwh'           => ['kode' => 'rpkwh',           'nama' => 'Rp/kWh Jual',          'satuan' => 'Rp/kWh','kategori' => 'tariff'],
            'pertumbuhan_yoy' => ['kode' => 'pertumbuhan_yoy', 'nama' => '% Pertumbuhan YoY',    'satuan' => '%',      'kategori' => 'growth'],
        ];
    }
}