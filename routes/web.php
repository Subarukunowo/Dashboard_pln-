<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TwoFactorController;

// ===== PUBLIC =====
Route::get('/api/dashboard/bpp', [DashboardController::class, 'bpp']);
Route::post('/api/chatbot', [ChatbotController::class, 'ask']);

// ===== GUEST ONLY =====
Route::middleware('guest')->group(function () {
    Route::get('/login', fn() => view('auth.login'))->name('login');
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
});

// ===== AUTH (2FA setup & verify — belum perlu 2fa verified) =====
Route::middleware('auth')->group(function () {
    Route::get('/2fa/setup',    [TwoFactorController::class, 'setup'])->name('2fa.setup');
    Route::post('/2fa/enable',  [TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::get('/2fa/verify',   [TwoFactorController::class, 'verify'])->name('2fa.verify');
    Route::post('/2fa/check',   [TwoFactorController::class, 'check'])->name('2fa.check');
    Route::post('/2fa/disable', [TwoFactorController::class, 'disable'])->name('2fa.disable');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// ===== AUTH + 2FA =====
Route::middleware(['auth', '2fa'])->group(function () {
    Route::get('/',                    fn() => view('dashboard'));
    Route::get('/dashboard',           fn() => view('dashboard'))->name('dashboard');
    Route::get('/realisasi-penjualan', fn() => view('realisasi-penjualan'));
    Route::get('/penjualan-kwh',       fn() => view('penjualan-kwh'));
});