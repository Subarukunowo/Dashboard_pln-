<?php

namespace App\Http\Controllers;

use App\Http\Requests\DimSubGolongan\StoreRtDimSubGolonganRequest;
use App\Http\Requests\DimSubGolongan\UpdateRtDimSubGolonganRequest;
use App\Services\RtDimSubGolonganService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RtDimSubGolonganController extends BaseApiController
{
    public function __construct(private readonly RtDimSubGolonganService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['golongan_kode', 'search']);
        $data    = $request->boolean('all')
            ? $this->service->getAll($filters)
            : $this->service->getPaginated($request->integer('per_page', 15), $filters);

        return $this->success($data);
    }

    public function show(string $kode): JsonResponse
    {
        $record = $this->service->findById($kode);

        return $record
            ? $this->success($record)
            : $this->notFound();
    }

    public function store(StoreRtDimSubGolonganRequest $request): JsonResponse
    {
        $record = $this->service->create($request->validated());

        return $this->created($record);
    }

    public function update(UpdateRtDimSubGolonganRequest $request, string $kode): JsonResponse
    {
        $record = $this->service->update($kode, $request->validated());

        return $this->success($record, 'Data berhasil diperbarui');
    }

    public function destroy(string $kode): JsonResponse
    {
        $this->service->delete($kode);

        return $this->noContent();
    }
}
