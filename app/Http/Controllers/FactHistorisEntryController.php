<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\Models\DimGolongan;
use App\Models\DimMetrik;
use App\Models\DimPeriode;
use App\Models\FactHistoris;
use App\Services\FactHistorisEntryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FactHistorisEntryController extends Controller
{
    public function __construct(private FactHistorisEntryService $entryService) {}

    public function create()
    {
        $tahunDenganData = FactHistoris::where('is_akumulasi', 0)
            ->where('is_jumlah_tahunan', 0)
            ->distinct()
            ->pluck('tahun')
            ->toArray();

        $tahunMulai = 2026;
        $tahunAkhir = now()->year + 2;
        
        $tahunTersedia = collect(range($tahunMulai, $tahunAkhir))
            ->filter(fn($t) => !in_array($t, $tahunDenganData))
            ->values();

        $metrikList = DimMetrik::whereIn('kode', ['kwh_jual','mwh_jual','rupiah','pelanggan','daya_va','rpkwh'])
                              ->orderBy('kategori')->orderBy('nama')->get();
        $golonganList = DimGolongan::orderBy('urutan')->get();
        
        $currentYear = now()->year;
        $currentMonth = now()->month;
        
        $defaultTahun = $tahunTersedia->firstWhere(fn($t) => $t == $currentYear) 
                      ?? $tahunTersedia->first() 
                      ?? $tahunMulai;
        
        return view('partials.fact-historis-entry', compact(
            'tahunTersedia',
            'metrikList',
            'golonganList',
            'currentYear',
            'currentMonth',
            'defaultTahun'
        ));
    }

    public function getExistingData(Request $request)
    {
        try {
            $request->validate([
                'tahun' => 'required|integer',
                'bulan' => 'required|integer|between:1,12',
            ]);

            $tahun = (int) $request->tahun;
            $bulan = (int) $request->bulan;

            if ($tahun < 2026) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fitur input data hanya tersedia untuk tahun 2026 ke atas.',
                    'locked' => true,
                ], 403);
            }

            $tahunDenganData = FactHistoris::where('tahun', $tahun)
                ->where('is_akumulasi', 0)
                ->where('is_jumlah_tahunan', 0)
                ->exists();
                
            if ($tahun < now()->year && $tahunDenganData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tahun ini sudah final dan tidak dapat diedit.',
                    'locked' => true,
                ], 403);
            }

            $canEdit = $this->entryService->canEditPeriod($tahun, $bulan);
            
            if (!$canEdit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data untuk periode ini sudah tidak dapat diedit karena sudah melewati minggu pertama bulan berikutnya.',
                    'locked' => true,
                ]);
            }

            $data = FactHistoris::where('tahun', $tahun)
                ->where('bulan', $bulan)
                ->where('is_akumulasi', 0)
                ->where('is_jumlah_tahunan', 0)
                ->with(['metrik', 'golongan'])
                ->get()
                ->groupBy('metrik_kode');

            return response()->json([
                'success' => true,
                'data' => $data,
                'locked' => false,
                'canEdit' => $canEdit,
            ]);

        } catch (\Exception $e) {
            Log::error("getExistingData error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'locked' => true,
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer|min:2026|max:2099',
            'bulan' => 'required|integer|between:1,12',
            'data' => 'required|array',
            'data.*.metrik_kode' => 'required|string|exists:dim_metrik,kode',
            'data.*.golongan_kode' => 'required|string|exists:dim_golongan,kode',
            'data.*.nilai' => 'required|numeric',
        ]);

        $tahun = (int) $request->tahun;
        $bulan = (int) $request->bulan;
        $dataEntries = $request->data;

        if ($tahun < 2026) {
            return response()->json([
                'success' => false,
                'message' => 'Input data hanya diperbolehkan untuk tahun 2026 ke atas.',
            ], 403);
        }

        if (!$this->entryService->canEditPeriod($tahun, $bulan)) {
            return response()->json([
                'success' => false,
                'message' => 'Periode ini sudah melewati batas waktu input.',
            ], 403);
        }

        try {
            DB::beginTransaction();

            $updatedCount = 0;
            $createdCount = 0;

            foreach ($dataEntries as $entry) {
                $metrikKode = strtolower(trim($entry['metrik_kode']));
                $golonganKode = strtoupper(trim($entry['golongan_kode']));
                $nilai = (float) $entry['nilai'];

                $existing = FactHistoris::where('tahun', $tahun)
                    ->where('bulan', $bulan)
                    ->where('metrik_kode', $metrikKode)
                    ->where('golongan_kode', $golonganKode)
                    ->where('is_akumulasi', 0)
                    ->where('is_jumlah_tahunan', 0)
                    ->first();

                if ($existing) {
                    if ($existing->nilai != $nilai) {
                        $existing->update(['nilai' => $nilai, 'sumber' => 'manual']);
                        $updatedCount++;
                    }
                } else {
                    FactHistoris::create([
                        'tahun' => $tahun,
                        'bulan' => $bulan,
                        'metrik_kode' => $metrikKode,
                        'golongan_kode' => $golonganKode,
                        'nilai' => $nilai,
                        'is_triwulan' => 0,
                        'is_akumulasi' => 0,
                        'is_jumlah_tahunan' => 0,
                        'sumber' => 'manual',
                    ]);
                    $createdCount++;
                }
            }

            DB::commit();

            $totalChanges = $updatedCount + $createdCount;
            if ($totalChanges > 0) {
                ActivityLogger::log(
                    $createdCount > 0 && $updatedCount > 0 ? 'data_historis_create_update' : 
                    ($createdCount > 0 ? 'data_historis_create' : 'data_historis_update'),
                    "Input data historis: " . $this->getBulanName($bulan) . " {$tahun}",
                    ['tahun' => $tahun, 'bulan' => $bulan, 'created' => $createdCount, 'updated' => $updatedCount],
                    $request
                );
            }

            return response()->json([
                'success' => true,
                'message' => "Berhasil menyimpan {$totalChanges} data",
                'created' => $createdCount,
                'updated' => $updatedCount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("store error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function getBulanName(int $bulan): string
    {
        $bulanNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        return $bulanNames[$bulan] ?? '';
    }
}