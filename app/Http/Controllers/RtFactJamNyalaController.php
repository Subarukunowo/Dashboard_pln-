<?php

namespace App\Http\Controllers;

use App\Http\Requests\JamNyala\StoreRtFactJamNyalaRequest;
use App\Http\Requests\JamNyala\UpdateRtFactJamNyalaRequest;
use App\Services\RtFactJamNyalaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RtFactJamNyalaController extends BaseApiController
{
    public function __construct(private readonly RtFactJamNyalaService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['tahun', 'bulan', 'tipe', 'sumber', 'golongan_kode', 'is_triwulan', 'is_akumulasi']);
        $data    = $this->service->getPaginated($request->integer('per_page', 15), $filters);

        return $this->success($data);
    }

    public function show(int $id): JsonResponse
    {
        $record = $this->service->findById($id);

        return $record ? $this->success($record) : $this->notFound();
    }

    public function store(StoreRtFactJamNyalaRequest $request): JsonResponse
    {
        $record = $this->service->create($request->validated());

        return $this->created($record);
    }

    public function update(UpdateRtFactJamNyalaRequest $request, int $id): JsonResponse
    {
        $record = $this->service->update($id, $request->validated());

        return $this->success($record, 'Data berhasil diperbarui');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return $this->noContent();
    }

    public function chart(Request $request): JsonResponse
    {
        $tahun = $request->integer('tahun', now()->year);
        $tipe  = $request->input('tipe', 'realisasi');

        return $this->success($this->service->getChartData($tahun, $tipe));
    }

    public function destroyByFilter(Request $request): JsonResponse
    {
        $request->validate([
            'tahun' => 'required|integer|min:2000|max:2100',
            'tipe'  => 'nullable|in:realisasi,target,semua',
        ]);

        $tahun = $request->integer('tahun');
        $tipe  = $request->input('tipe');

        $query = $this->service->getModel()->where('tahun', $tahun);
        if ($tipe && $tipe !== 'semua') {
            $query->where('tipe', $tipe);
        }
        $deleted = $query->delete();

        return $this->success(['deleted' => $deleted], 'Data berhasil dihapus');
    }

    /**
     * Buat data 1 tahun penuh untuk SEMUA golongan_kode di rt_dim_sub_golongan,
     * tidak peduli sudah ada isinya atau belum. Mengembalikan rincian
     * created/skipped/failed supaya kelihatan jelas kalau ada yang gagal.
     */
    public function buatTahun(Request $request): JsonResponse
    {
        $request->validate([
            'tahun' => 'required|integer|min:2000|max:2100',
            'tipe'  => 'required|in:realisasi,target',
        ]);

        $tahun  = $request->integer('tahun');
        $result = $this->service->createTahun($tahun, $request->input('tipe'));

        $message = $result['failed'] > 0
            ? "Sebagian gagal: {$result['created']} berhasil, {$result['skipped']} sudah ada, {$result['failed']} gagal (cek log server)."
            : "Data jam nyala tahun {$tahun} berhasil dibuat: {$result['created']} baris baru, {$result['skipped']} sudah ada sebelumnya, dari total {$result['total_kode']} golongan.";

        return $this->success($result, $message);
    }

    /**
     * Summary per golongan induk (S, R, B, I, P, M) + TOTAL.
     */
    public function summary(Request $request): JsonResponse
    {
        $request->validate([
            'tahun' => 'required|integer|min:2000|max:2100',
            'tipe'  => 'nullable|in:realisasi,target',
        ]);

        $tahun = $request->integer('tahun');
        $tipe  = $request->input('tipe', 'realisasi');

        return $this->success($this->service->getSummaryByGolongan($tahun, $tipe));
    }

    /**
     * Daftar tahun yang benar-benar punya data — untuk select tahun
     * di modal Muat dan Hapus.
     */
    public function tahunTersedia(): JsonResponse
    {
        return $this->success($this->service->getAvailableYears());
    }

    /**
     * Detail Target + Realisasi (12 bulan) untuk satu golongan_kode.
     * Golongan yang belum punya data tetap dikembalikan (nilai null).
     */
    public function detail(Request $request): JsonResponse
    {
        $request->validate([
            'tahun'         => 'required|integer|min:2000|max:2100',
            'golongan_kode' => 'required|string',
        ]);

        $data = $this->service->getDetailByGolongan(
            $request->integer('tahun'),
            $request->input('golongan_kode')
        );

        return $this->success($data);
    }
}