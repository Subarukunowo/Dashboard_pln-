<?php

namespace App\Services;

use App\Models\DimGolongan;
use App\Models\DimMetrik;
use App\Models\DimPeriode;
use App\Models\FactHistoris;
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
        'S' => '#ff9800', 'R' => '#00ff88', 'B' => '#2196f3',
        'I' => '#e91e63', 'P' => '#9c27b0', 'M' => '#00bcd4',
        'TOTAL' => '#ffffff', 'JUMLAH' => '#aaaaaa',
    ];

    public function getChartData(int $tahunDari, int $tahunSampai, string $metrikKode, array $golonganList, string $mode = 'tahunan'): array
    {
        $isMixed = $this->isMixed($tahunDari, $tahunSampai);
        if ($isMixed && $mode === 'bulanan') {
            $mode = 'triwulan';
        }

        // DB menggunakan lowercase (mwh_jual), UI mungkin kirim PascalCase
        $metrikFilter = strtolower(trim($metrikKode));

        Log::info("DEBUG_CHART_START", [
            'input_metrik' => $metrikKode,
            'filter_metrik' => $metrikFilter,
            'golongan' => $golonganList,
            'range' => "$tahunDari - $tahunSampai"
        ]);

        $raw = FactHistoris::whereBetween('tahun', [$tahunDari, $tahunSampai])
            ->where('metrik_kode', $metrikFilter)
            ->whereIn('golongan_kode', $golonganList)
            ->where('is_akumulasi', 0)
            ->orderBy('tahun', 'asc')
            ->orderBy('bulan', 'asc')
            ->get();

        Log::info("DEBUG_CHART_ROWS", ['count' => $raw->count()]);

        $byGolTahunBulan = [];
        foreach ($raw as $r) {
            $byGolTahunBulan[$r->golongan_kode][$r->tahun][$r->bulan] = [
                'nilai' => (float)$r->nilai,
                'is_triwulan' => (bool)$r->is_triwulan,
                'is_jumlah' => (bool)$r->is_jumlah_tahunan,
            ];
        }

        return match ($mode) {
            'tahunan' => $this->buildTahunan($byGolTahunBulan, $golonganList, $tahunDari, $tahunSampai, $metrikKode),
            'triwulan' => $this->buildTriwulan($byGolTahunBulan, $golonganList, $tahunDari, $tahunSampai, $metrikKode),
            'bulanan' => $this->buildBulanan($byGolTahunBulan, $golonganList, $tahunDari, $tahunSampai, $metrikKode),
            default => $this->buildTahunan($byGolTahunBulan, $golonganList, $tahunDari, $tahunSampai, $metrikKode),
        };
    }

    private function buildTahunan(array $data, array $golonganList, int $dari, int $sampai, string $metrik): array
    {
        $labels = range($dari, $sampai);
        $datasets = [];

        foreach ($golonganList as $kode) {
            $values = [];
            foreach ($labels as $tahun) {
                $bulanData = $data[$kode][$tahun] ?? [];
                
                $jumlahRow = null;
                foreach($bulanData as $b) {
                    if($b['is_jumlah']) { $jumlahRow = $b; break; }
                }

                if ($jumlahRow) {
                    $values[] = $jumlahRow['nilai'];
                } else {
                    $sum = 0;
                    $hasData = false;
                    foreach($bulanData as $b) {
                        if(!$b['is_jumlah']) { $sum += $b['nilai']; $hasData = true; }
                    }
                    $values[] = $hasData ? $sum : null;
                }
            }
            $datasets[] = $this->makeDataset($kode, $values);
        }
        return ['labels' => array_map('strval', $labels), 'datasets' => $datasets, 'mode' => 'tahunan'];
    }

    private function buildTriwulan(array $data, array $golonganList, int $dari, int $sampai, string $metrik): array
    {
        $labels = [];
        for ($t = $dari; $t <= $sampai; $t++) {
            foreach (self::TW_BULAN as $tw => $bulan) { $labels[] = "{$t}-TW{$tw}"; }
        }

        $datasets = [];
        foreach ($golonganList as $kode) {
            $values = [];
            for ($t = $dari; $t <= $sampai; $t++) {
                $bulanData = $data[$kode][$t] ?? [];
                $hasTriwulan = false;
                foreach($bulanData as $b) { if($b['is_triwulan']) $hasTriwulan = true; }

                foreach (self::TW_BULAN as $tw => $representBulan) {
                    if ($hasTriwulan) {
                        $values[] = $bulanData[$representBulan]['nilai'] ?? null;
                    } else {
                        $sum = 0; $found = false;
                        foreach (self::TW_RANGE[$tw] as $b) {
                            if (isset($bulanData[$b]) && !$bulanData[$b]['is_jumlah']) {
                                $sum += $bulanData[$b]['nilai']; $found = true;
                            }
                        }
                        $values[] = $found ? $sum : null;
                    }
                }
            }
            $datasets[] = $this->makeDataset($kode, $values);
        }
        return ['labels' => $labels, 'datasets' => $datasets, 'mode' => 'triwulan'];
    }

    private function buildBulanan(array $data, array $golonganList, int $dari, int $sampai, string $metrik): array
    {
        $bulanNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $labels = [];
        for ($t = $dari; $t <= $sampai; $t++) {
            foreach ($bulanNames as $bn) { $labels[] = "{$t}-{$bn}"; }
        }

        $datasets = [];
        foreach ($golonganList as $kode) {
            $values = [];
            for ($t = $dari; $t <= $sampai; $t++) {
                $bulanData = $data[$kode][$t] ?? [];
                for ($b = 1; $b <= 12; $b++) {
                    $row = $bulanData[$b] ?? null;
                    $values[] = ($row && !$row['is_jumlah']) ? $row['nilai'] : null;
                }
            }
            $datasets[] = $this->makeDataset($kode, $values);
        }
        return ['labels' => $labels, 'datasets' => $datasets, 'mode' => 'bulanan'];
    }
    

    private function makeDataset(string $kode, array $values): array
    {
        $gol = DimGolongan::where('kode', $kode)->first();
        $nama = $gol ? $gol->nama : $kode;
        $color = self::COLORS[$kode] ?? '#aaaaaa';

        return [
            'label' => $nama,
            'golongan' => $kode,
            'data' => $values,
            'borderColor' => $color,
            'backgroundColor' => $color,
        ];
    }

    public function isMixed(int $dari, int $sampai): bool
    {
        return DimPeriode::whereBetween('tahun', [$dari, $sampai])
            ->pluck('granularitas')
            ->unique()
            ->count() > 1;
    }
}