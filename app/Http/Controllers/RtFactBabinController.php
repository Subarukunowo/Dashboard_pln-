<?php

namespace App\Http\Controllers;

use App\Http\Requests\Babin\StoreRtFactBabinRequest;
use App\Http\Requests\Babin\UpdateRtFactBabinRequest;
use App\Services\RtFactBabinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RtFactBabinController extends BaseApiController
{
    public function __construct(private readonly RtFactBabinService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['tahun', 'bulan', 'tipe', 'area', 'sumber', 'is_akumulasi']);
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

    public function store(StoreRtFactBabinRequest $request): JsonResponse
    {
        $record = $this->service->create($request->validated());

        return $this->created($record);
    }

    public function update(UpdateRtFactBabinRequest $request, int $id): JsonResponse
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
    $tahun  = $request->integer('tahun', now()->year);
    $metrik = $request->input('metrik', 'kwh'); // 'kwh' atau 'rp'

    return $this->success($this->service->getAllChartData($tahun, $metrik));
}
}
