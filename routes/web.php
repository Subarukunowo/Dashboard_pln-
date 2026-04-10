<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\AuthController;

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
Route::get('/penjualan-kwh', function () {
    return view('penjualan-kwh');
});
Route::post('/api/chatbot', [ChatbotController::class, 'ask']);
Route::middleware('guest')->group(function () {
    Route::get('/login', fn() => view('auth.login'))->name('login');
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});