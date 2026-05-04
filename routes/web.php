<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\HistorisController;
use App\Http\Controllers\FactHistorisEntryController;

// ===== PUBLIC API =====
Route::get('/api/dashboard/bpp', [DashboardController::class, 'bpp']);
Route::post('/api/chatbot', [ChatbotController::class, 'ask']);

// ===== GUEST ONLY =====
Route::middleware('guest')->group(function () {
    Route::get('/login', fn() => view('auth.login'))->name('login');
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
});

// ===== AUTH ONLY — 2FA + logout =====
Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/2fa/setup', function () {
        $user = auth()->user();
        if ($user->two_factor_enabled && session('2fa_verified')) {
            return redirect()->route($user->hasRole('admin') ? 'admin.dashboard' : 'dashboard');
        }
        return app(TwoFactorController::class)->setup();
    })->name('2fa.setup');

    Route::post('/2fa/enable',  [TwoFactorController::class, 'enable'])->name('2fa.enable');

    Route::get('/2fa/verify', function () {
        $user = auth()->user();
        if (session('2fa_verified')) {
            return redirect()->route($user->hasRole('admin') ? 'admin.dashboard' : 'dashboard');
        }
        if (!$user->two_factor_enabled) {
            return redirect()->route('2fa.setup');
        }
        return app(TwoFactorController::class)->verify();
    })->name('2fa.verify');

    Route::post('/2fa/check',   [TwoFactorController::class, 'check'])->name('2fa.check');
    Route::post('/2fa/disable', [TwoFactorController::class, 'disable'])->name('2fa.disable');
});

// ===== AUTH + 2FA — User =====
Route::middleware(['auth', '2fa'])->group(function () {
    Route::get('/',          fn() => view('user.dashboard'));
    Route::get('/dashboard', fn() => view('user.dashboard'))->name('dashboard');
    Route::get('/realisasi-penjualan', fn() => view('user.realisasi-penjualan'));
    Route::get('/penjualan-kwh',       fn() => view('user.penjualan-kwh'));
});

// ===== AUTH + 2FA + ADMIN — Admin Panel =====
Route::prefix('admin')->name('admin.')->middleware(['auth', '2fa', 'admin'])->group(function () {
    Route::get('/',    [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users',  [AdminController::class, 'users'])->name('users');
    Route::post('/users/{user}/toggle',    [AdminController::class, 'toggleUser'])->name('users.toggle');
    Route::post('/users/{user}/reset-2fa', [AdminController::class, 'resetTwoFactor'])->name('users.reset2fa');

    // Devices — approve & reject ditangani DeviceController
    Route::get('/devices',                    [AdminController::class, 'devices'])->name('devices');
 Route::any('/devices/{id}/approve', [DeviceController::class, 'approve'])->name('devices.approve');
    Route::post('/devices/{id}/reject',       [DeviceController::class, 'reject'])->name('devices.reject');
    Route::delete('/devices/{device}/revoke', [DeviceController::class, 'destroy'])->name('devices.revoke');

    Route::get('/logs', [AdminController::class, 'logs'])->name('logs');
});

// ===== HISTORIS =====
Route::get('/historis',        [HistorisController::class, 'index'])->name('historis.index');
Route::get('/historis/chart',  [HistorisController::class, 'chart'])->name('historis.chart');
Route::get('/historis/table',  [HistorisController::class, 'table'])->name('historis.table');
Route::get('/historis/metrik', [HistorisController::class, 'availableMetrik'])->name('historis.metrik');

// ===== FACT HISTORIS ENTRY =====
Route::middleware(['auth'])->group(function () {
    Route::get('/fact-historis/create',         [FactHistorisEntryController::class, 'create'])->name('fact-historis.create');
    Route::get('/fact-historis/existing-data',  [FactHistorisEntryController::class, 'getExistingData'])->name('fact-historis.existing-data');
    Route::post('/fact-historis',               [FactHistorisEntryController::class, 'store'])->name('fact-historis.store');
});
Route::get('/debug', function () {
    return [
        'MAIL_MAILER (raw env)' => env('MAIL_MAILER'),
        'mail.default (config)' => config('mail.default'),
        '.env loaded?' => file_exists(base_path('.env')) ? 'YES' : 'NO',
    ];
}); 
Route::middleware(['auth'])->group(function () {
    Route::get('/fact-historis', [FactHistorisEntryController::class, 'create'])->name('fact-historis.create');
    Route::get('/fact-historis/existing-data', [FactHistorisEntryController::class, 'getExistingData'])->name('fact-historis.existing');
    Route::post('/fact-historis', [FactHistorisEntryController::class, 'store'])->name('fact-historis.store');
    
    // ✅ Tambah route import Excel (konsisten dengan controller existing)
    Route::post('/fact-historis/import-excel', [FactHistorisEntryController::class, 'importExcel'])
        ->name('fact-historis.import-excel');
         // [NEW] POST: Import from Excel
    Route::post('/fact-historis/import-excel', [FactHistorisEntryController::class, 'importExcel'])->name('fact-historis.import-excel');
    
    // [NEW] GET: Download template
    Route::get('/fact-historis/download-template', [FactHistorisEntryController::class, 'downloadTemplate'])->name('fact-historis.download-template');
});
 
    