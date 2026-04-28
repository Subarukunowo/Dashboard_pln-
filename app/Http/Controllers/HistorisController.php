<?php

namespace App\Http\Controllers;

use App\Models\DimGolongan;
use App\Models\DimMetrik;
use App\Models\DimPeriode;
use App\Models\FactHistoris;
use App\Services\HistorisQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HistorisController extends Controller
{
    public function __construct(private HistorisQueryService $svc) {}

    // ── Halaman utama ──────────────────────────────────────────────────────────
    public function index()
    {
        $tahunTersedia      = DimPeriode::orderBy('tahun')->pluck('tahun');
        $currentYear        = now()->year;
        $tahunInputTersedia = collect(range($currentYear, $currentYear + 2));

        $metrikList = DimMetrik::whereIn('kode', [
                          'kwh_jual', 'mwh_jual', 'rupiah',
                          'pelanggan', 'daya_va', 'rpkwh',
                          'pertumbuhan_yoy', 'growth',
                      ])
                      ->orderBy('kategori')->orderBy('nama')->get();

        $golonganList = DimGolongan::orderBy('urutan')->get();

        return view('partials.history.Historis', compact(
            'tahunTersedia',
            'tahunInputTersedia',
            'currentYear',
            'metrikList',
            'golonganList'
        ));
    }

    // ── Chart data ─────────────────────────────────────────────────────────────
    public function chart(Request $request)
    {
        $request->validate([
            'tahun_dari'   => 'required|integer|min:1997|max:2099',
            'tahun_sampai' => 'required|integer|min:1997|max:2099',
            'metrik'       => 'required|string',
            'golongan'     => 'required|array|min:1',
            'golongan.*'   => 'string',
            'mode'         => 'nullable|in:tahunan,bulanan,triwulan',
        ]);

        $dari     = (int) $request->tahun_dari;
        $sampai   = (int) $request->tahun_sampai;
        $metrik   = strtolower(trim($request->metrik));
        $golongan = (array) $request->golongan;
        $mode     = $request->mode ?? 'tahunan';
        $isMixed  = $this->svc->isMixed($dari, $sampai);

        if ($isMixed && $mode === 'bulanan') {
            $mode = 'triwulan';
        }

        // ── YoY on-the-fly ────────────────────────────────────────────────────
        if ($metrik === 'pertumbuhan_yoy') {
            $data = $this->buildYoyChart($dari, $sampai, 'pelanggan', 'Pertumbuhan YoY Pelanggan (%)', '#00bcd4');
            return response()->json(['chart' => $data, 'is_mixed' => false, 'mode' => 'tahunan', 'warning' => $data['warning'] ?? null]);
        }

        if ($metrik === 'growth') {
            $data = $this->buildYoyChart($dari, $sampai, 'kwh_jual', 'Growth KWH Jual (%)', '#ff9800');
            return response()->json(['chart' => $data, 'is_mixed' => false, 'mode' => 'tahunan', 'warning' => $data['warning'] ?? null]);
        }

        // ── Metrik biasa ──────────────────────────────────────────────────────
        $data = $this->svc->getChartData($dari, $sampai, $metrik, $golongan, $mode);

        return response()->json([
            'chart'    => $data,
            'is_mixed' => $isMixed,
            'mode'     => $mode,
            'warning'  => $isMixed
                ? "Range {$dari}-{$sampai} mengandung data triwulan & bulanan. Mode otomatis: triwulan."
                : null,
        ]);
    }

    // ── Metrik yang tersedia untuk rentang tahun ───────────────────────────────
    public function availableMetrik(Request $request)
    {
        $dari   = (int) ($request->query('dari')   ?? 1997);
        $sampai = (int) ($request->query('sampai') ?? 2025);

        $kodes = collect($this->svc->getAvailableMetrik($dari, $sampai))
            ->pluck('kode')
            ->toArray();

        // Tambahkan metrik YoY kalau data sumbernya tersedia di range ini
        if (in_array('pelanggan', $kodes)) $kodes[] = 'pertumbuhan_yoy';
        if (in_array('kwh_jual', $kodes))  $kodes[] = 'growth';

        return response()->json(
            DimMetrik::whereIn('kode', array_unique($kodes))
                ->orderBy('kategori')->orderBy('nama')->get()
        );
    }

    // ── Data tabel detail per tahun ────────────────────────────────────────────
    public function table(Request $request)
    {
        $request->validate([
            'tahun'  => 'required|integer',
            'metrik' => 'required|string|exists:dim_metrik,kode',
        ]);

        $rows = FactHistoris::with(['golongan', 'metrik'])
            ->tahun($request->tahun)
            ->metrik($request->metrik)
            ->orderBy('bulan')
            ->orderByRaw("FIELD(golongan_kode,'S','R','B','I','P','M','TOTAL')")
            ->get();

        $periode = DimPeriode::find($request->tahun);
        $metrik  = DimMetrik::find($request->metrik);

        return response()->json([
            'rows'        => $rows,
            'periode'     => $periode,
            'metrik'      => $metrik,
            'is_triwulan' => $periode?->isTriwulan(),
        ]);
    }

    // =========================================================================
    // PRIVATE — Kalkulasi YoY / Growth on-the-fly
    // =========================================================================

    /**
     * Hitung pertumbuhan YoY untuk metrik apapun.
     *
     * Strategi:
     * 1. Pakai is_jumlah_tahunan=1, bulan=0, golongan_kode='TOTAL'
     *    → nilai ini konsisten & tidak perlu sum per golongan
     * 2. Fallback: sum semua golongan (S+R+B+I+P+M) kalau TOTAL tidak ada
     *
     * YoY(T) = (nilai_T - nilai_(T-1)) / nilai_(T-1) × 100
     */
    private function buildYoyChart(int $dari, int $sampai, string $metrikKode, string $label, string $color): array
    {
        // Ambil dari (dari-1) supaya bisa hitung YoY tahun pertama
        $tahunDari = $dari - 1;

        // ── Strategi 1: pakai TOTAL langsung ─────────────────────────────────
        $rows = FactHistoris::whereBetween('tahun', [$tahunDari, $sampai])
            ->whereRaw('LOWER(metrik_kode) = ?', [strtolower($metrikKode)])
            ->where('golongan_kode', 'TOTAL')
            ->where('is_jumlah_tahunan', 1)
            ->where('bulan', 0)
            ->where('is_akumulasi', 0)
            ->orderBy('tahun')
            ->get(['tahun', 'nilai']);

        $totalPerTahun = [];
        foreach ($rows as $row) {
            $totalPerTahun[$row->tahun] = (float) $row->nilai;
        }

        // ── Strategi 2 fallback: sum S+R+B+I+P+M ─────────────────────────────
        if (empty($totalPerTahun)) {
            $rowsFallback = FactHistoris::whereBetween('tahun', [$tahunDari, $sampai])
                ->whereRaw('LOWER(metrik_kode) = ?', [strtolower($metrikKode)])
                ->whereIn('golongan_kode', ['S', 'R', 'B', 'I', 'P', 'M'])
                ->where('is_jumlah_tahunan', 1)
                ->where('bulan', 0)
                ->where('is_akumulasi', 0)
                ->orderBy('tahun')
                ->get(['tahun', 'golongan_kode', 'nilai']);

            foreach ($rowsFallback as $row) {
                $totalPerTahun[$row->tahun] = ($totalPerTahun[$row->tahun] ?? 0) + (float) $row->nilai;
            }
        }

        ksort($totalPerTahun);

        Log::info("buildYoyChart [{$metrikKode}]", [
            'dari'   => $dari,
            'sampai' => $sampai,
            'tahun'  => array_keys($totalPerTahun),
        ]);

        if (empty($totalPerTahun)) {
            return [
                'labels'   => [],
                'datasets' => [],
                'mode'     => 'tahunan',
                'warning'  => "Tidak ada data {$metrikKode} (is_jumlah_tahunan=1) untuk range {$dari}–{$sampai}.",
            ];
        }

        // Hitung YoY — skip tahun base (dari-1)
        $labels    = [];
        $yoyValues = [];

        foreach ($totalPerTahun as $tahun => $nilaiIni) {
            if ($tahun < $dari) continue;

            $nilaiLalu = $totalPerTahun[$tahun - 1] ?? null;

            if ($nilaiLalu !== null && $nilaiLalu != 0) {
                $yoyValues[] = round((($nilaiIni - $nilaiLalu) / $nilaiLalu) * 100, 2);
            } else {
                $yoyValues[] = null;
            }

            $labels[] = (string) $tahun;
        }

        return [
            'labels'   => $labels,
            'datasets' => [[
                'label'           => $label,
                'golongan'        => 'YOY',
                'data'            => $yoyValues,
                'borderColor'     => $color,
                'backgroundColor' => $color,
            ]],
            'mode'     => 'tahunan',
            'warning'  => null,
        ];
    }
}