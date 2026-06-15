<?php

namespace App\Http\Controllers;

use App\Services\RtFactKvaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RtFactKvaController extends BaseApiController
{
    public function __construct(private readonly RtFactKvaService $service) {}

    public function chart(Request $request): JsonResponse
    {
        $tahun     = $request->integer('tahun', now()->year);
        $tahunLalu = $request->integer('tahun_lalu', $tahun - 1);

        return $this->success($this->service->getChartData($tahun, $tahunLalu));
    }
}