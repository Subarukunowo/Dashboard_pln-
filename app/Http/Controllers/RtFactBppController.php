<?php

namespace App\Http\Controllers;

use App\Http\Requests\Bpp\StoreRtFactBppRequest;
use App\Http\Requests\Bpp\UpdateRtFactBppRequest;
use App\Services\RtFactBppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RtFactBppController extends BaseApiController
{
    public function __construct(private readonly RtFactBppService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['tahun', 'bulan', 'versi', 'jenis_bpp', 'sumber']);
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

    public function store(StoreRtFactBppRequest $request): JsonResponse
    {
        $record = $this->service->create($request->validated());

        return $this->created($record);
    }

    public function update(UpdateRtFactBppRequest $request, int $id): JsonResponse
    {
        $record = $this->service->update($id, $request->validated());

        return $this->success($record, 'Data berhasil diperbarui');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return $this->noContent();
    }
}
