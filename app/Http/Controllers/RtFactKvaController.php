<?php

namespace App\Http\Controllers;

use App\Http\Requests\Kva\StoreFactKvaRequest;
use App\Http\Requests\Kva\UpdateFactKvaRequest;
use App\Services\RtFactKvaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RtFactKvaController extends BaseApiController
{
    public function __construct(private readonly RtFactKvaService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['tahun', 'bulan', 'tipe', 'sumber', 'golongan_kode', 'sub_golongan_kode', 'is_triwulan', 'is_akumulasi']);
        $data    = $this->service->getPaginated($request->integer('per_page', 15), $filters);

        return $this->success($data);
    }

    public function show(int $id): JsonResponse
    {
        $record = $this->service->findById($id);

        return $record ? $this->success($record) : $this->notFound();
    }

    public function store(StoreFactKvaRequest $request): JsonResponse
    {
        $record = $this->service->create($request->validated());

        return $this->created($record);
    }

    public function update(UpdateFactKvaRequest $request, int $id): JsonResponse
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

    /**
     * Generate 12 baris/bulan data kosong (nilai=0) untuk satu tahun+tipe,
     * mencakup semua sub_golongan_kode dari rt_dim_sub_golongan
     * (tarif asli + JUMLAH-S/R/B/I/P/M + JUMLAH/TOTAL).
     */
    public function buatTahun(Request $request): JsonResponse
    {
        $request->validate([
            'tahun' => 'required|integer|min:2000|max:2100',
            'tipe'  => 'required|in:realisasi,target',
        ]);

        $result = $this->service->buatTahun(
            $request->integer('tahun'),
            $request->input('tipe')
        );

        return $this->created($result, 'Data KVA berhasil dibuat');
    }

    public function destroyByFilter(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tahun' => 'required|integer|min:2000|max:2100',
            'tipe'  => 'nullable|in:realisasi,target',
        ]);

        $result = $this->service->hapusTahun($validated['tahun'], $validated['tipe'] ?? null);

        return $this->success($result, 'Data KVA berhasil dihapus');
    }

    /**
     * Update satu titik data (sub golongan + bulan + tipe), lalu otomatis
     * rehitung JUMLAH-{golongan}, JUMLAH total, triwulan, dan akumulasi.
     */
    public function updateNilai(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tahun'             => 'required|integer|min:2000|max:2100',
            'bulan'             => 'required|integer|min:1|max:12',
            'sub_golongan_kode' => 'required|string|max:30',
            'tipe'              => 'required|in:realisasi,target',
            'nilai_kva'         => 'required|numeric',
        ]);

        $result = $this->service->updateNilaiDanRehitung(
            $validated['tahun'],
            $validated['bulan'],
            $validated['sub_golongan_kode'],
            $validated['tipe'],
            (float) $validated['nilai_kva']
        );

        return $this->success($result, 'Data KVA berhasil diupdate');
    }
}