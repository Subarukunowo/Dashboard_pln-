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
    private const GOLONGAN_PELANGGAN = ['S', 'R', 'B', 'I', 'P', 'M'];

    public function __construct(private HistorisQueryService $svc) {}

    // ── Halaman utama ──────────────────────────────────────────────────────────
    public function index()
    {
        $tahunTersedia      = DimPeriode::orderBy('tahun')->pluck('tahun');
        $currentYear        = now()->year;
        $tahunInputTersedia = collect(range($currentYear, $currentYear + 2));

        $metrikList = DimMetrik::whereIn('kode', [
                          'kwh_jual', 'mwh_jual', 'rupiah',
                          'pelanggan', 'daya_va', 'rpkwh', 'pertumbuhan_yoy'
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

        // ── Pertumbuhan YoY: hitung on-the-fly ────────────────────────────────
        if ($metrik === 'pertumbuhan_yoy') {
            $data = $this->buildYoyChart($dari, $sampai);

            return response()->json([
                'chart'    => $data,
                'is_mixed' => $isMixed,
                'mode'     => $data['mode'],
                'warning'  => $data['warning'] ?? null,
            ]);
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

        // Kalau pelanggan ada di range ini, YoY bisa dihitung
        if (in_array('pelanggan', $kodes) && !in_array('pertumbuhan_yoy', $kodes)) {
            $kodes[] = 'pertumbuhan_yoy';
        }

        return response()->json(
            DimMetrik::whereIn('kode', $kodes)->orderBy('kategori')->orderBy('nama')->get()
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
    // PRIVATE — Kalkulasi YoY on-the-fly
    // =========================================================================

    /**
     * Hitung pertumbuhan YoY pelanggan per tahun.
     *
     * Kunci: pakai is_jumlah_tahunan=1, bulan=0
     * → nilai ini ada di semua era (1997-2004 triwulan & 2005+ bulanan)
     * → tidak perlu sum per bulan, tidak kena masalah mixed granularitas
     *
     * YoY(T) = (total_T - total_(T-1)) / total_(T-1) × 100
     */
    private function buildYoyChart(int $dari, int $sampai): array
    {
        // Ambil dari (dari-1) supaya bisa hitung YoY tahun pertama
        $rows = FactHistoris::whereBetween('tahun', [$dari - 1, $sampai])
            ->whereRaw('LOWER(metrik_kode) = ?', ['pelanggan'])
            ->whereIn('golongan_kode', self::GOLONGAN_PELANGGAN)
            ->where('is_jumlah_tahunan', 1)
            ->where('bulan', 0)
            ->where('is_akumulasi', 0)
            ->orderBy('tahun')
            ->get(['tahun', 'golongan_kode', 'nilai']);

        Log::info('buildYoyChart', [
            'dari'   => $dari,
            'sampai' => $sampai,
            'rows'   => $rows->count(),
        ]);

        // Jumlahkan semua golongan (S+R+B+I+P+M) per tahun
        $totalPerTahun = [];
        foreach ($rows as $row) {
            $totalPerTahun[$row->tahun] = ($totalPerTahun[$row->tahun] ?? 0) + (float) $row->nilai;
        }
        ksort($totalPerTahun);

        Log::info('totalPerTahun', $totalPerTahun);

        if (empty($totalPerTahun)) {
            return [
                'labels'   => [],
                'datasets' => [],
                'mode'     => 'tahunan',
                'warning'  => 'Tidak ada data pelanggan (is_jumlah_tahunan=1) untuk range ini.',
            ];
        }

        // Hitung YoY per tahun, skip tahun base (dari-1)
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
                'label'           => 'Pertumbuhan YoY Pelanggan (%)',
                'golongan'        => 'YOY',
                'data'            => $yoyValues,
                'borderColor'     => '#00bcd4',
                'backgroundColor' => '#00bcd4',
            ]],
            'mode'     => 'tahunan',
            'warning'  => null,
        ];
    }
}