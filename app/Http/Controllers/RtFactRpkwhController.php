<?php

namespace App\Http\Controllers;

use App\Http\Requests\Rpkwh\StoreRtFactRpkwhRequest;
use App\Http\Requests\Rpkwh\UpdateRtFactRpkwhRequest;
use App\Services\RtFactRpkwhService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RtFactRpkwhController extends BaseApiController
{
    public function __construct(private readonly RtFactRpkwhService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['tahun', 'bulan', 'tipe', 'sumber', 'golongan_kode', 'sub_golongan_kode', 'is_triwulan', 'is_akumulasi']);
        $data    = $this->service->getPaginated($request->integer('per_page', 15), $filters);

        return $this->success($data);
    }

    public function show(int $id): JsonResponse
    {
        $record = $this->service->findById($id);

        return $record
            ? $this->success($record)
            : $this->notFound();
    }

    public function store(StoreRtFactRpkwhRequest $request): JsonResponse
    {
        $record = $this->service->create($request->validated());

        return $this->created($record);
    }

    public function update(UpdateRtFactRpkwhRequest $request, int $id): JsonResponse
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
        $tahun     = $request->integer('tahun', now()->year);
        $tahunLalu = $request->integer('tahun_lalu', $tahun - 1);

        return $this->success($this->service->getChartData($tahun, $tahunLalu));
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
     * Buat data awal (nilai 0) untuk sebuah tahun, mencakup semua sub-golongan
     * dari master rt_dim_sub_golongan. Dipakai tombol "Buat Data".
     */
    public function buatTahun(Request $request): JsonResponse
    {
        $request->validate([
            'tahun' => 'required|integer|min:2000|max:2100',
            'tipe'  => 'required|in:realisasi,target',
        ]);

        $tahun = $request->integer('tahun');
        $tipe  = $request->input('tipe');

        try {
            $jumlah = $this->service->buatTahun($tahun, $tipe);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(['jumlah' => $jumlah], "Data RPKWH tahun {$tahun} berhasil dibuat");
    }
}