<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HistorisQueryService;
use Illuminate\Http\Request;

class HistorisPelangganStatsController extends Controller
{
    // Semua golongan yang mau dijumlahkan — sesuaikan dengan golongan_kode di DB
    private const GOLONGAN_ALL = ['S', 'R', 'B', 'I', 'P', 'M'];

    public function __construct(private HistorisQueryService $historis) {}

    public function index(Request $request)
    {
        $tahun = (int) $request->get('tahun', now()->year);
        $bulan = (int) $request->get('bulan', now()->month);

        $nilaiTahunIni  = $this->getTotalAkumulasi($tahun,     $bulan);
        $nilaiTahunLalu = $this->getTotalAkumulasi($tahun - 1, $bulan);

        $yoy = null;
        if ($nilaiTahunLalu > 0) {
            $yoy = (($nilaiTahunIni - $nilaiTahunLalu) / $nilaiTahunLalu) * 100;
        }

        return response()->json([
            'tahun'            => $tahun,
            'bulan'            => $bulan,
            'nilai_tahun_ini'  => $nilaiTahunIni,
            'nilai_tahun_lalu' => $nilaiTahunLalu,
            'yoy_persen'       => $yoy !== null ? round($yoy, 2) : null,
        ]);
    }

    /**
     * Ambil data semua golongan sekaligus, lalu jumlahkan per bulan 1...$sampai.
     * Lebih efisien: 1x query ke DB untuk semua golongan.
     */
    private function getTotalAkumulasi(int $tahun, int $sampaibulan): float
    {
        $chartData = $this->historis->getChartData(
            $tahun, $tahun, 'pelanggan', self::GOLONGAN_ALL, 'bulanan'
        );

        if (empty($chartData['datasets'])) {
            return 0;
        }

        $bulanNames    = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $targetBulans  = array_slice($bulanNames, 0, $sampaibulan); // ['Jan','Feb',...] s.d bulan ke-N
        $labels        = $chartData['labels'] ?? [];                // ["2025-Jan", "2025-Feb", ...]

        // Kumpulkan index label yang masuk rentang bulan
        $validIndexes = [];
        foreach ($labels as $i => $label) {
            $part = explode('-', $label)[1] ?? '';
            if (in_array($part, $targetBulans)) {
                $validIndexes[] = $i;
            }
        }

        // Jumlahkan semua golongan × semua bulan valid
        $total = 0;
        foreach ($chartData['datasets'] as $ds) {
            foreach ($validIndexes as $i) {
                $total += (float) ($ds['data'][$i] ?? 0);
            }
        }

        return $total;
    }
}