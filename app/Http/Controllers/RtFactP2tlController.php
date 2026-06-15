<?php

namespace App\Http\Controllers;

use App\Http\Requests\P2tl\StoreRtFactP2tlRequest;
use App\Http\Requests\P2tl\UpdateRtFactP2tlRequest;
use App\Services\RtFactP2tlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RtFactP2tlController extends BaseApiController
{
    public function __construct(private readonly RtFactP2tlService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['tahun', 'bulan', 'tipe', 'sumber', 'is_triwulan', 'is_akumulasi']);
        $data    = $this->service->getPaginated($request->integer('per_page', 15), $filters);

        return $this->success($data);
    }

    public function show(int $id): JsonResponse
    {
        $record = $this->service->findById($id);

        return $record ? $this->success($record) : $this->notFound();
    }

    public function store(StoreRtFactP2tlRequest $request): JsonResponse
    {
        $record = $this->service->create($request->validated());

        return $this->created($record);
    }

    public function update(UpdateRtFactP2tlRequest $request, int $id): JsonResponse
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
}