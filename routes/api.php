<?php

use Illuminate\Support\Facades\Route;

// ✅ Health check - test dulu yang ini
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