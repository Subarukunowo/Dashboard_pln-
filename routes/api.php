<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HistorisPelangganStatsController; // ← ADD THIS LINE

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