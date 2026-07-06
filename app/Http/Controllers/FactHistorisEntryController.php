<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\Models\DimGolongan;
use App\Models\DimMetrik;
use App\Models\DimPeriode;
use App\Models\FactHistoris;
use App\Services\FactHistorisEntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\FactHistorisExcelImport;

class FactHistorisEntryController extends Controller
{
    public function __construct(private FactHistorisEntryService $entryService) {}

    /**
     * Tampilkan form input dengan filter tahun.
     */
    public function create()
    {
        $now = now();
        $tahunTersedia = $this->entryService->getAvailableYears(2026, 2);

        $metrikList = DimMetrik::whereIn('kode', [
                'kwh_jual','mwh_jual','rupiah','pelanggan','daya_va','rpkwh','pertumbuhan_yoy'
            ])
            ->orderBy('kategori')
            ->orderBy('nama')
            ->get();

        $golonganList = DimGolongan::orderBy('urutan')->get();
        $defaultTahun = $tahunTersedia->first() ?? 2026;
        $defaultBulan = $now->month;

        return view('partials.history.FactHistorisEntry', compact(
            'tahunTersedia', 'metrikList', 'golonganList', 'defaultTahun', 'defaultBulan'
        ));
    }

    /**
     * Ambil data existing untuk periode tertentu.
     */
    public function getExistingData(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer',
            'bulan' => 'required|integer|between:1,12',
        ]);

        try {
            return $this->buildExistingDataResponse($request);
        } catch (\Exception $e) {
            Log::error("getExistingData error", [
                'message' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'request' => $request->all(),
            ]);
            return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage(), 'locked' => true], 500);
        }
    }

    private function buildExistingDataResponse(Request $request): JsonResponse
    {
        $tahun = (int) $request->tahun;
        $bulan = (int) $request->bulan;

        if ($tahun < 2026) {
            return response()->json([
                'success' => false, 'message' => 'Data historis 1997–2025 hanya bisa dibaca.', 'locked' => true,
            ], 403);
        }

        $status = $this->entryService->getPeriodeStatus($tahun, $bulan);
        if (!$status['can_edit']) {
            return response()->json([
                'success' => false, 'message' => $status['reason'], 'locked' => true,
                'deadline' => $status['deadline'],
            ]);
        }

        $transformedData = $this->fetchTransformedRecords($tahun, $bulan);

        return response()->json([
            'success' => true, 'data' => $transformedData, 'locked' => false,
            'canEdit' => true, 'deadline' => $status['deadline'], 'reason' => $status['reason'],
        ]);
    }

    private function fetchTransformedRecords(int $tahun, int $bulan)
    {
        $records = FactHistoris::where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->where('is_akumulasi', 0)
            ->where('is_jumlah_tahunan', 0)
            ->select('id', 'metrik_kode', 'golongan_kode', 'nilai')
            ->get();

        return $records->groupBy('metrik_kode')->map(function ($items) {
            return $items->map(function ($item) {
                return [
                    'golongan_kode' => $item->golongan_kode,
                    'nilai' => $item->nilai !== null ? (float) $item->nilai : null,
                ];
            })->values();
        });
    }

    /**
     * Simpan data manual dari form.
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

        if ($guard = $this->validateStoreRequest($tahun, $bulan)) {
            return $guard;
        }

        try {
            return $this->saveEntries($request, $tahun, $bulan);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("store error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }
    }

    private function validateStoreRequest(int $tahun, int $bulan): ?JsonResponse
    {
        if ($tahun < 2026) {
            return response()->json(['success' => false, 'message' => 'Input data hanya diperbolehkan untuk tahun 2026 ke atas.'], 403);
        }

        if (!$this->entryService->canEditPeriod($tahun, $bulan)) {
            return response()->json(['success' => false, 'message' => 'Periode ini sudah melewati batas waktu input.'], 403);
        }

        return null;
    }

    private function saveEntries(Request $request, int $tahun, int $bulan): JsonResponse
    {
        DB::beginTransaction();
        $createdCount = 0;
        $updatedCount = 0;

        foreach ($request->data as $entry) {
            $result = $this->processEntry($tahun, $bulan, $entry);
            $createdCount += $result === 'created' ? 1 : 0;
            $updatedCount += $result === 'updated' ? 1 : 0;
        }

        if ($createdCount > 0 || $updatedCount > 0) {
            $this->entryService->ensureDimPeriode($tahun);
        }

        DB::commit();
        $totalChanges = $updatedCount + $createdCount;

        if ($totalChanges > 0) {
            ActivityLogger::log(
                $this->resolveActivityAction($createdCount, $updatedCount),
                "Input data historis: " . $this->getBulanName($bulan) . " {$tahun}",
                ['tahun' => $tahun, 'bulan' => $bulan, 'created' => $createdCount, 'updated' => $updatedCount],
                $request
            );
        }

        return response()->json([
            'success' => true, 'message' => "Berhasil menyimpan {$totalChanges} data",
            'created' => $createdCount, 'updated' => $updatedCount,
        ]);
    }

    private function processEntry(int $tahun, int $bulan, array $entry): string
    {
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

        if (!$existing) {
            FactHistoris::create([
                'tahun' => $tahun, 'bulan' => $bulan, 'metrik_kode' => $metrikKode,
                'golongan_kode' => $golonganKode, 'nilai' => $nilai,
                'is_triwulan' => 0, 'is_akumulasi' => 0, 'is_jumlah_tahunan' => 0, 'sumber' => 'manual',
            ]);
            return 'created';
        }

        if ($existing->nilai != $nilai) {
            $existing->update(['nilai' => $nilai, 'sumber' => 'manual']);
            return 'updated';
        }

        return 'unchanged';
    }

    private function resolveActivityAction(int $createdCount, int $updatedCount): string
    {
        if ($createdCount > 0 && $updatedCount > 0) {
            return 'data_historis_create_update';
        }

        if ($createdCount > 0) {
            return 'data_historis_create';
        }

        return 'data_historis_update';
    }

    /**
     * [NEW] Import data dari Excel template.
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'tahun'      => 'required|integer|min:2026|max:2099',
            'bulan'      => 'required|integer|between:1,12',
            'sumber'     => 'nullable|string|max:100',
        ], [
            'file_excel.mimes' => 'Format file harus .xlsx, .xls, atau .csv',
            'file_excel.max'   => 'Ukuran file maksimal 10MB',
        ]);

        $tahun = (int) $request->tahun;
        $bulan = (int) $request->bulan;

        if ($guard = $this->validateEditableExcelPeriod($tahun, $bulan)) {
            return $guard;
        }

        return $this->handleExcelImport($request, $tahun, $bulan);
    }

    private function validateEditableExcelPeriod(int $tahun, int $bulan): ?JsonResponse
    {
        if ($tahun < 2026) {
            return response()->json(['success' => false, 'message' => 'Import hanya diperbolehkan untuk tahun 2026 ke atas.'], 403);
        }

        if (!$this->entryService->canEditPeriod($tahun, $bulan)) {
            return response()->json(['success' => false, 'message' => 'Periode ini sudah terkunci dan tidak dapat diimpor.'], 423);
        }

        return null;
    }

    private function handleExcelImport(Request $request, int $tahun, int $bulan): JsonResponse
    {
        $sumber = $request->sumber ?? 'excel_import';

        try {
            DB::beginTransaction();

            $models = $this->parseExcelModels($request->file('file_excel'), $tahun, $bulan, $sumber);
            [$createdCount, $updatedCount] = $this->upsertFactHistorisModels($models);

            if ($createdCount > 0 || $updatedCount > 0) {
                $this->entryService->ensureDimPeriode($tahun);
            }

            DB::commit();
            $total = $createdCount + $updatedCount;

            if ($total > 0) {
                ActivityLogger::log(
                    'data_historis_import_excel',
                    "Import Excel: {$this->getBulanName($bulan)} {$tahun}",
                    ['tahun' => $tahun, 'bulan' => $bulan, 'imported' => $total],
                    $request
                );
            }

            return response()->json([
                'success' => true, 'message' => 'Data berhasil diimpor dari Excel',
                'imported' => $total, 'created' => $createdCount, 'updated' => $updatedCount,
            ], 200);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            $errors = collect($e->failures())
                ->map(fn ($f) => "Baris {$f->row()}: " . implode(', ', $f->errors()))
                ->toArray();
            return response()->json(['success' => false, 'message' => 'Validasi file gagal', 'errors' => $errors], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('FactHistorisEntry importExcel error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(), 'request' => $request->all(),
            ]);
            return response()->json(['success' => false, 'message' => 'Gagal memproses file: ' . $e->getMessage()], 500);
        }
    }

    private function parseExcelModels($file, int $tahun, int $bulan, string $sumber): array
    {
        $import = new FactHistorisExcelImport($tahun, $bulan, $sumber);
        $models = [];

        Excel::import(new class($import, $models) implements
            \Maatwebsite\Excel\Concerns\WithMapping,
            \Maatwebsite\Excel\Concerns\WithHeadingRow
        {
            private $import;
            private $models;

            public function __construct($import, &$models)
            {
                $this->import = $import;
                $this->models = &$models;
            }

            public function headingRow(): int
            {
                return 1;
            }

            public function map($row): array
            {
                $result = $this->import->model($row);
                if (is_array($result)) {
                    $this->models = array_merge($this->models, $result);
                }
                return $row;
            }
        }, $file);

        return $models;
    }

    private function upsertFactHistorisModels(array $models): array
    {
        $createdCount = 0;
        $updatedCount = 0;

        foreach ($models as $model) {
            $attrs = $model->getAttributes();
            $existing = FactHistoris::where('tahun', $attrs['tahun'])
                ->where('bulan', $attrs['bulan'])
                ->where('metrik_kode', $attrs['metrik_kode'])
                ->where('golongan_kode', $attrs['golongan_kode'])
                ->where('is_akumulasi', 0)
                ->where('is_jumlah_tahunan', 0)
                ->first();

            if (!$existing) {
                FactHistoris::create($attrs);
                $createdCount++;
                continue;
            }

            if ($existing->nilai != $attrs['nilai']) {
                $existing->update(['nilai' => $attrs['nilai'], 'satuan' => $attrs['satuan'], 'sumber' => $attrs['sumber']]);
                $updatedCount++;
            }
        }

        return [$createdCount, $updatedCount];
    }

    /**
     * [NEW] Download template Excel kosong.
     */
    public function downloadTemplate()
    {
        $filePath = storage_path('app/templates/template_historis.xlsx');

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File template tidak ditemukan. Hubungi administrator.');
        }

        $fileName = 'Template_Fact_Historis_' . now()->format('Y') . '.xlsx';

        return response()->download($filePath, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function getBulanName(int $bulan): string
    {
        $bulanNames = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
        return $bulanNames[$bulan] ?? '';
    }
}