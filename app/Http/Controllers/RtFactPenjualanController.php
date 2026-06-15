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
}
