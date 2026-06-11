<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HistorisPelangganStatsController; 
use App\Http\Controllers\RtDimSubGolonganController;
use App\Http\Controllers\RtFactBabinController;
use App\Http\Controllers\RtFactBppController;
use App\Http\Controllers\RtFactHjrController;
use App\Http\Controllers\RtFactJamNyalaController;
use App\Http\Controllers\RtFactLossesController;
use App\Http\Controllers\RtFactP2tlController;
use App\Http\Controllers\RtFactPelangganController;
use App\Http\Controllers\RtFactPendapatanController;
use App\Http\Controllers\RtFactPenjualanController;
use App\Http\Controllers\RtFactRpkwhController;
use App\Http\Controllers\RtImportLogsController;

Route::get('/historis/pelanggan-stats', [HistorisPelangganStatsController::class, 'index']);

Route::get('/sheets/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'test' => 'Routes working!'
    ]);
});

// ✅ Query endpoint - POST only
Route::post('/sheets/query', function (\Illuminate\Http\Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'Proxy endpoint works!',
        'received' => $request->all(),
        'timestamp' => now()->toISOString(),
    ]);
});
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // Dimension
    Route::apiResource('dim/sub-golongan', RtDimSubGolonganController::class)
        ->parameters(['sub-golongan' => 'kode']);
    // Fact tables
    Route::apiResource('fact/babin',      RtFactBabinController::class);
    Route::apiResource('fact/bpp',        RtFactBppController::class);
    Route::apiResource('fact/hjr',        RtFactHjrController::class);
    Route::apiResource('fact/jam-nyala',  RtFactJamNyalaController::class);
    Route::apiResource('fact/losses',     RtFactLossesController::class);
    Route::apiResource('fact/p2tl',       RtFactP2tlController::class);
    Route::apiResource('fact/pelanggan',  RtFactPelangganController::class);
    Route::apiResource('fact/pendapatan', RtFactPendapatanController::class);
    Route::apiResource('fact/penjualan',  RtFactPenjualanController::class);
    Route::apiResource('fact/rpkwh',      RtFactRpkwhController::class);

    // Import logs (read + create only; no update)
    Route::apiResource('import-logs', RtImportLogsController::class)
        ->except(['update']);
});
