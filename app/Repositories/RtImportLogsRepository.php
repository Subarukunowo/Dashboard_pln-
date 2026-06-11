<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportLogs\StoreRtImportLogsRequest;
use App\Services\RtImportLogsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RtImportLogsController extends BaseApiController
{
    public function __construct(private readonly RtImportLogsService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['tahun', 'bulan', 'user_id', 'sheet_name', 'search']);
        $data    = $this->service->getPaginatedWithUser($filters);

        return $this->success($data);
    }

    public function show(int $id): JsonResponse
    {
        $record = $this->service->findById($id);

        return $record
            ? $this->success($record)
            : $this->notFound();
    }

    public function store(StoreRtImportLogsRequest $request): JsonResponse
    {
        $record = $this->service->create($request->validated());

        return $this->created($record);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return $this->noContent();
    }
}
