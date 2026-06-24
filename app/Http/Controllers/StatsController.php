<?php

namespace App\Http\Controllers;

use App\Http\Requests\Stats\StatsRequest;
use App\Services\StatsService;

class StatsController extends Controller
{
    private const VALID_SECTIONS = [
        'bpp', 'losses', 'penjualan', 'babin', 'jamnyala',
        'goltarif', 'pendapatan', 'rpkwh', 'pelanggan', 'kva',
        'tunggakan', 'p2tl',
    ];

    public function __construct(
        protected StatsService $service
    ) {}

// sementara di StatsController@show, tambah try-catch
public function show(string $section, StatsRequest $request)
{
    if (!in_array($section, self::VALID_SECTIONS, true)) {
        return response()->json(['message' => 'Section tidak ditemukan.'], 404);
    }

    try {
        $data = $this->service->getStats(
            $section,
            $request->tahun(),
            $request->bulan()
        );
        return response()->json($data);
    } catch (\Throwable $e) {
        \Log::error('Stats error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
}