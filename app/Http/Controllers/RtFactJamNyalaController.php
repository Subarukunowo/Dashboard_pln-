<?php

namespace App\Http\Controllers;

use App\Http\Requests\JamNyala\StoreRtFactJamNyalaRequest;
use App\Http\Requests\JamNyala\UpdateRtFactJamNyalaRequest;
use App\Services\RtFactJamNyalaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RtFactJamNyalaController extends BaseApiController
{
    public function __construct(private readonly RtFactJamNyalaService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['tahun', 'bulan', 'tipe', 'sumber', 'golongan_kode', 'is_triwulan', 'is_akumulasi']);
        $data    = $this->service->getPaginated($request->integer('per_page', 15), $filters);

        return $this->success($data);
    }

    public function show(int $id): JsonResponse
    {
        $record = $this->service->findById($id);

        return $record ? $this->success($record) : $this->notFound();
    }

    public function store(StoreRtFactJamNyalaRequest $request): JsonResponse
    {
        $record = $this->service->create($request->validated());

        return $this->created($record);
    }

    public function update(UpdateRtFactJamNyalaRequest $request, int $id): JsonResponse
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