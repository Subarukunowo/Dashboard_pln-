<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('dashboard');
});
Route::get('/api/dashboard/bpp', [DashboardController::class, 'bpp']);
Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/realisasi-penjualan', function () {
    return view('realisasi-penjualan');
});