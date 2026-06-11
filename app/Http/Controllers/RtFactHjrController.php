<?php

namespace App\Http\Controllers;

use App\Http\Requests\Hjr\StoreRtFactHjrRequest;
use App\Http\Requests\Hjr\UpdateRtFactHjrRequest;
use App\Services\RtFactHjrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RtFactHjrController extends BaseApiController
{
    public function __construct(private readonly RtFactHjrService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['tahun', 'bulan', 'tipe', 'sumber', 'tarif_kode', 'tegangan', 'is_triwulan', 'is_akumulasi']);
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

    public function store(StoreRtFactHjrRequest $request): JsonResponse
    {
        $record = $this->service->create($request->validated());

        return $this->created($record);
    }

    public function update(UpdateRtFactHjrRequest $request, int $id): JsonResponse
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
