<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HistorisPelangganStatsController;
use App\Http\Controllers\RtDatabaseStatsController;
use App\Http\Controllers\RtDimSubGolonganController;
use App\Http\Controllers\RtFactBabinController;
use App\Http\Controllers\RtFactBppController;
use App\Http\Controllers\RtFactHjrController;
use App\Http\Controllers\RtFactJamNyalaController;
use App\Http\Controllers\RtFactLossesController;
use App\Http\Controllers\RtFactProduksiController;
use App\Http\Controllers\RtFactP2tlController;
use App\Http\Controllers\RtFactPelangganController;
use App\Http\Controllers\RtFactKvaController;
use App\Http\Controllers\RtFactPendapatanController;
use App\Http\Controllers\RtFactPenjualanController;
use App\Http\Controllers\RtFactRpkwhController;
use App\Http\Controllers\RtFactTunggakanController;
use App\Http\Controllers\RtImportLogsController;
use App\Http\Controllers\StatsController;

// ── Internal-only endpoints (HMAC signed, tidak bisa diakses dari browser/Postman) ─────
//
//   Semua request harus menyertakan:
//     X-Api-Token     : HMAC-SHA256(INTERNAL_API_SECRET, timestamp)
//     X-Api-Timestamp : unix timestamp saat request dibuat (valid ±60 detik)
//
//   Token di-generate oleh blade via helper internal_api_token() dan
//   otomatis di-inject oleh apiFetch() di apiClient.js.
// ─────────────────────────────────────────────────────────────────────────────────────────

Route::middleware('internal.api')->group(function () {

    Route::get('/historis/pelanggan-stats', [HistorisPelangganStatsController::class, 'index']);
    Route::get('/stats/database',           [RtDatabaseStatsController::class, 'index']);

    Route::get('/sheets/health', fn() => response()->json([
        'status'    => 'ok',
        'timestamp' => now()->toISOString(),
    ]));

    Route::post('/sheets/query', fn(\Illuminate\Http\Request $r) => response()->json([
        'success'   => true,
        'received'  => $r->all(),
        'timestamp' => now()->toISOString(),
    ]));

    // Chart endpoints — diakses frontend dashboard
    Route::prefix('chart')->group(function () {
        Route::get('bpp',       [RtFactBppController::class,       'chart']);
        Route::get('hjr',       [RtFactHjrController::class,       'chart']);
        Route::get('babin',     [RtFactBabinController::class,     'chart']);
        Route::get('jam-nyala', [RtFactJamNyalaController::class,  'chart']);
        Route::get('losses',    [RtFactLossesController::class,    'chart']);
        Route::get('p2tl',      [RtFactP2tlController::class,      'chart']);
        Route::get('pelanggan', [RtFactPelangganController::class,  'chart']);
        Route::get('kva',       [RtFactKvaController::class,        'chart']);
        Route::get('pendapatan',[RtFactPendapatanController::class, 'chart']);
        Route::get('penjualan', [RtFactPenjualanController::class,  'chart']);
        Route::get('rpkwh',     [RtFactRpkwhController::class,      'chart']);
        Route::get('tunggakan', [RtFactTunggakanController::class,  'chart']);
    });

    Route::get('/stats/{section}', [StatsController::class, 'show']);

});

// ── Authenticated endpoints (Sanctum token) ───────────────────────────────────────────
Route::middleware(['internal.api', 'auth:sanctum'])->group(function () {
    Route::delete('fact/jam-nyala/filter',     [RtFactJamNyalaController::class,   'destroyByFilter']);
    Route::delete('v1/fact/pelanggan/filter',  [RtFactPelangganController::class,  'destroyByFilter']);
    Route::delete('v1/fact/penjualan/filter',  [RtFactPenjualanController::class,  'destroyByFilter']);
    Route::delete('v1/fact/pendapatan/filter', [RtFactPendapatanController::class, 'destroyByFilter']);
    Route::delete('rt-fact-tunggakan-by-filter', [RtFactTunggakanController::class, 'destroyByFilter']);
});

Route::prefix('v1')->middleware(['internal.api', 'auth:sanctum'])->group(function () {

    Route::apiResource('dim/sub-golongan', RtDimSubGolonganController::class)
        ->parameters(['sub-golongan' => 'kode']);

    Route::apiResource('fact/babin',       RtFactBabinController::class);
    Route::apiResource('fact/bpp',         RtFactBppController::class);
    Route::apiResource('fact/hjr',         RtFactHjrController::class);
    Route::apiResource('fact/jam-nyala',   RtFactJamNyalaController::class);
    Route::apiResource('fact/losses',      RtFactLossesController::class);
    Route::apiResource('rt-fact-produksi', RtFactProduksiController::class);
    Route::apiResource('fact/p2tl',        RtFactP2tlController::class);
    Route::apiResource('fact/pelanggan',   RtFactPelangganController::class);
    Route::apiResource('fact/pendapatan',  RtFactPendapatanController::class);
    Route::apiResource('fact/penjualan',   RtFactPenjualanController::class);
    Route::apiResource('fact/rpkwh',       RtFactRpkwhController::class);
    Route::apiResource('fact/tunggakan',   RtFactTunggakanController::class);

    Route::apiResource('import-logs', RtImportLogsController::class)
        ->except(['update']);
});
