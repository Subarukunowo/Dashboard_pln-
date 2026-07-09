<?php

namespace App\Http\Controllers;

use App\Http\Requests\Penjualan\StoreRtFactPenjualanRequest;
use App\Http\Requests\Penjualan\UpdateRtFactPenjualanRequest;
use App\Services\RtFactPenjualanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RtFactPenjualanController extends BaseApiController
{
    public function __construct(private readonly RtFactPenjualanService $service) {}

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

    public function store(StoreRtFactPenjualanRequest $request): JsonResponse
    {
        $record = $this->service->create($request->validated());

        return $this->created($record);
    }

    public function update(UpdateRtFactPenjualanRequest $request, int $id): JsonResponse
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
        return $this->success($this->service->getChartData($tahun));
    }

    /**
     * Generate 12 baris/bulan data kosong (nilai=0) untuk satu tahun+tipe,
     * berdasarkan SEMUA baris rt_dim_sub_golongan (tarif asli + JUMLAH-S/R/B/I/P/M
     * + JUMLAH/TOTAL grand-total) — pola sama seperti modul pelanggan.
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

        return $this->created($result, 'Data berhasil dibuat');
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
}