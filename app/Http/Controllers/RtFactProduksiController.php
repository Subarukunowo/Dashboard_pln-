<?php

namespace App\Http\Controllers;

use App\Http\Requests\Produksi\StoreRtFactProduksiRequest;
use App\Http\Requests\Produksi\UpdateRtFactProduksiRequest;
use App\Services\RtFactProduksiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RtFactProduksiController extends BaseApiController
{
    public function __construct(private readonly RtFactProduksiService $service) {}

    public function index(Request $request): JsonResponse
    {
        // tipe_losses ditambahkan supaya upsert() di CrudHelper.js bisa
        // mencari record yang tepat (tahun+bulan+tipe_losses), bukan cuma
        // tahun+bulan — karena sekarang 1 bulan bisa punya 2 varian data
        // (dengan_emin & tanpa_emin).
        $filters = $request->only(['tahun', 'bulan', 'tipe_losses']);
        $data    = $this->service->getPaginated($request->integer('per_page', 15), $filters);

        return $this->success($data);
    }

    public function show(int $id): JsonResponse
    {
        $record = $this->service->findById($id);

        return $record ? $this->success($record) : $this->notFound();
    }

    public function store(StoreRtFactProduksiRequest $request): JsonResponse
    {
        $record = $this->service->create($request->validated());

        return $this->created($record);
    }

    public function update(UpdateRtFactProduksiRequest $request, int $id): JsonResponse
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