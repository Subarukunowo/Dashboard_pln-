<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tunggakan\StoreRtFactTunggakanRequest;
use App\Http\Requests\Tunggakan\UpdateRtFactTunggakanRequest;
use App\Services\RtFactTunggakanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RtFactTunggakanController extends BaseApiController
{
    public function __construct(private readonly RtFactTunggakanService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['tahun', 'bulan', 'sumber']);
        $data    = $this->service->getPaginated($request->integer('per_page', 15), $filters);

        return $this->success($data);
    }

    public function show(int $id): JsonResponse
    {
        $record = $this->service->findById($id);

        return $record ? $this->success($record) : $this->notFound();
    }

    public function store(StoreRtFactTunggakanRequest $request): JsonResponse
    {
        $record = $this->service->create($request->validated());

        return $this->created($record);
    }

    public function update(UpdateRtFactTunggakanRequest $request, int $id): JsonResponse
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

    public function destroyByFilter(Request $request): JsonResponse
    {
        $request->validate([
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        $tahun   = $request->integer('tahun');
        $deleted = $this->service->getModel()->where('tahun', $tahun)->delete();

        return $this->success(['deleted' => $deleted], 'Data berhasil dihapus');
    }
}