<?php

namespace App\Http\Controllers;

use App\Models\DimGolongan;
use App\Models\DimMetrik;
use App\Models\DimPeriode;
use App\Models\FactHistoris;
use App\Services\HistorisQueryService;
use Illuminate\Http\Request;

class HistorisController extends Controller
{
    public function __construct(private HistorisQueryService $svc) {}

    // ── Halaman utama ──────────────────────────────────────────────────────────
    public function index()
    {
        // Untuk filter VIEW data historis (1997-2025)
        $tahunTersedia = DimPeriode::orderBy('tahun')->pluck('tahun');
        
        // Untuk INPUT data baru (current year sampai +2)
        $currentYear = now()->year;
        $tahunInputTersedia = collect(range($currentYear, $currentYear + 2));
        
        $metrikList    = DimMetrik::whereIn('kode', ['kwh_jual','mwh_jual','rupiah','pelanggan','daya_va','rpkwh'])
                                  ->orderBy('kategori')->orderBy('nama')->get();
        $golonganList  = DimGolongan::orderBy('urutan')->get();

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

        // Paksa triwulan jika range mengandung data campuran
        if ($isMixed && $mode === 'bulanan') {
            $mode = 'triwulan';
        }

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

        $kodes = $this->svc->getAvailableMetrik($dari, $sampai);

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
}