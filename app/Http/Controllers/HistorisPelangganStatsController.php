<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FactHistoris;
use App\Services\HistorisQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HistorisPelangganStatsController extends Controller
{
    // ✅ Sesuaikan dengan hasil tinker — golongan_kode di fact_historis untuk metrik pelanggan
    // Contoh umum: ['S','R','B','I','P','M'] atau ['S','R','B','I','P','M','TOTAL']
    private const GOLONGAN_ALL = ['S', 'R', 'B', 'I', 'P', 'M'];

    public function __construct(private HistorisQueryService $historis) {}

    public function index(Request $request)
    {
        $tahun = (int) $request->get('tahun', now()->year);
        $bulan = (int) $request->get('bulan', now()->month);

        // Hitung total pelanggan akumulasi Jan s.d $bulan untuk dua tahun
        $nilaiIni  = $this->getTotalPelanggan($tahun,     $bulan);
        $nilaiLalu = $this->getTotalPelanggan($tahun - 1, $bulan);

        Log::info('PelangganStats', [
            'tahun'     => $tahun,
            'bulan'     => $bulan,
            'ini'       => $nilaiIni,
            'lalu'      => $nilaiLalu,
        ]);

        $yoy = null;
        if ($nilaiLalu > 0) {
            // YoY% = (tahun_ini - tahun_lalu) / tahun_lalu × 100
            $yoy = (($nilaiIni - $nilaiLalu) / $nilaiLalu) * 100;
        }

        return response()->json([
            'tahun'            => $tahun,
            'bulan'            => $bulan,
            'nilai_tahun_ini'  => $nilaiIni,
            'nilai_tahun_lalu' => $nilaiLalu,
            'yoy_persen'       => $yoy !== null ? round($yoy, 2) : null,
        ]);
    }

    /**
     * Hitung total pelanggan semua golongan dari bulan 1 s.d $sampaibulan.
     *
     * Strategi:
     * 1. Coba via HistorisQueryService (pakai GOLONGAN_ALL)
     * 2. Fallback: query langsung ke DB tanpa filter golongan
     *    → jumlahkan semua baris bulanan non-akumulasi non-jumlah-tahunan
     */
    private function getTotalPelanggan(int $tahun, int $sampaibulan): float
    {
        // ── Strategi 1: via HistorisQueryService ──────────────────────
        $total = $this->getTotalViaService($tahun, $sampaibulan);
        if ($total > 0) {
            return $total;
        }

        // ── Strategi 2: fallback query langsung ───────────────────────
        // Berguna kalau golongan_kode di DB berbeda dari GOLONGAN_ALL
        return $this->getTotalViaDirectQuery($tahun, $sampaibulan);
    }

    private function getTotalViaService(int $tahun, int $sampaibulan): float
    {
        try {
            $chartData = $this->historis->getChartData(
                $tahun, $tahun, 'pelanggan', self::GOLONGAN_ALL, 'bulanan'
            );

            return $this->sumChartDataUntilBulan($chartData, $sampaibulan);

        } catch (\Throwable $e) {
            Log::warning('getTotalViaService failed', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Jumlahkan semua golongan × bulan valid (Jan s.d $sampaibulan) dari hasil chart data.
     */
    private function sumChartDataUntilBulan(array $chartData, int $sampaibulan): float
    {
        if (empty($chartData['datasets'])) {
            return 0;
        }

        $bulanNames   = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $targetBulans = array_slice($bulanNames, 0, $sampaibulan); // Jan s.d bulan ke-N
        $labels       = $chartData['labels'] ?? [];                // ["2025-Jan", ...]

        // Index label yang masuk rentang bulan yang diminta
        $validIndexes = [];
        foreach ($labels as $i => $label) {
            $part = explode('-', $label)[1] ?? '';
            if (in_array($part, $targetBulans)) {
                $validIndexes[] = $i;
            }
        }

        if (empty($validIndexes)) {
            return 0;
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

    /**
     * Fallback: query langsung tanpa filter golongan.
     *
     * Ambil nilai bulan per bulan (is_triwulan=0, is_akumulasi=0, is_jumlah_tahunan=0)
     * lalu SUM dari bulan 1 s.d $sampaibulan.
     *
     * Catatan: untuk data 1997-2004 (triwulan), is_triwulan=1
     * sehingga tidak ikut sum — konsisten dengan mode bulanan.
     * Kalau ingin cover era triwulan juga, hapus kondisi is_triwulan=0.
     */
    private function getTotalViaDirectQuery(int $tahun, int $sampaibulan): float
    {
        $total = FactHistoris::where('tahun', $tahun)
            ->whereRaw('LOWER(metrik_kode) = ?', ['pelanggan'])
            ->where('is_akumulasi', 0)
            ->where('is_jumlah_tahunan', 0)
            ->where('is_triwulan', 0)
            ->whereBetween('bulan', [1, $sampaibulan])
            ->sum('nilai');

        return (float) $total;
    }
}