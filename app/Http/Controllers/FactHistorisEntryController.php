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

    /**
     * Tampilkan form input dengan filter tahun yang benar (Poin 2).
     */
    public function create()
    {
        $now = now();
        
        // Ambil daftar tahun yang MASIH BISA diedit berdasarkan logika Service
        $tahunTersedia = $this->entryService->getAvailableYears(2026, 2);

        $metrikList = DimMetrik::whereIn('kode', [
                'kwh_jual','mwh_jual','rupiah','pelanggan','daya_va','rpkwh','pertumbuhan_yoy'
            ])
            ->orderBy('kategori')
            ->orderBy('nama')
            ->get();
            
        $golonganList = DimGolongan::orderBy('urutan')->get();

        // Default: Ambil tahun pertama yang tersedia, atau fallback ke 2026
        $defaultTahun = $tahunTersedia->first() ?? 2026;
        $defaultBulan = $now->month;

        return view('partials.history.FactHistorisEntry', compact(
            'tahunTersedia',
            'metrikList',
            'golonganList',
            'defaultTahun',
            'defaultBulan'
        ));
    }

    /**
     * Ambil data existing untuk periode tertentu.
     * Lock logic diterapkan per bulan di sini (Poin 1).
     */
public function getExistingData(Request $request)
{
    try {
        $request->validate([
            'tahun' => 'required|integer',
            'bulan' => 'required|integer|between:1,12',
        ]);

        $tahun = (int) $request->tahun;
        $bulan = (int) $request->bulan;

        // 🔒 Lock historis data (1997-2025)
        if ($tahun < 2026) {
            return response()->json([
                'success' => false,
                'message' => 'Data historis 1997–2025 hanya bisa dibaca.',
                'locked'  => true,
            ], 403);
        }

        // 🔒 Cek status lock per bulan
        $status = $this->entryService->getPeriodeStatus($tahun, $bulan);

        if (!$status['can_edit']) {
            return response()->json([
                'success'  => false,
                'message'  => $status['reason'],
                'locked'   => true,
                'deadline' => $status['deadline'],
            ]);
        }

        // 📦 Ambil data dari database
        $records = FactHistoris::where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->where('is_akumulasi', 0)
            ->where('is_jumlah_tahunan', 0)
            ->select('id', 'metrik_kode', 'golongan_kode', 'nilai') // ✅ Hanya ambil field yang diperlukan
            ->get();

        // 🔄 Transform ke format yang diharapkan frontend:
        // { metrik_kode: [ { golongan_kode, nilai }, ... ] }
        $transformedData = $records->groupBy('metrik_kode')->map(function ($items) {
            return $items->map(function ($item) {
                return [
                    'golongan_kode' => $item->golongan_kode,
                    'nilai' => $item->nilai !== null ? (float) $item->nilai : null,
                ];
            })->values(); // ✅ Convert to indexed array
        });

        return response()->json([
            'success'  => true,
            'data'     => $transformedData,  // ✅ Format sudah bersih
            'locked'   => false,
            'canEdit'  => true,
            'deadline' => $status['deadline'],
            'reason'   => $status['reason'],
        ]);

    } catch (\Exception $e) {
        // 📝 Log error dengan detail lebih lengkap
        Log::error("getExistingData error", [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request' => $request->all(),
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage(),
            'locked'  => true,
            // ✅ Hapus 'trace' dari response production (security)
        ], 500);
    }
}
    /**
     * Simpan data dan update dim_periode (Poin 3).
     */
    public function store(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer|min:2026|max:2099',
            'bulan' => 'required|integer|between:1,12',
            'data'  => 'required|array',
            'data.*.metrik_kode'   => 'required|string',
            'data.*.golongan_kode' => 'required|string',
            'data.*.nilai'         => 'required|numeric',
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

        // Validasi lock per bulan (Poin 1)
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
                $metrikKode   = strtolower(trim($entry['metrik_kode']));
                $golonganKode = strtoupper(trim($entry['golongan_kode']));
                $nilai        = (float) $entry['nilai'];

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
                        'tahun'             => $tahun,
                        'bulan'             => $bulan,
                        'metrik_kode'       => $metrikKode,
                        'golongan_kode'     => $golonganKode,
                        'nilai'             => $nilai,
                        'is_triwulan'       => 0,
                        'is_akumulasi'      => 0,
                        'is_jumlah_tahunan' => 0,
                        'sumber'            => 'manual',
                    ]);
                    $createdCount++;
                }
            }

            // Poin 3: Update dim_periode dengan daftar metrik LENGKAP
            if ($createdCount > 0 || $updatedCount > 0) {
                $this->entryService->ensureDimPeriode($tahun);
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