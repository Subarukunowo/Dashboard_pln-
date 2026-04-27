<?php
// app/Http/Controllers/Api/FactHistorisController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FactHistoris;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FactHistorisController extends Controller
{
    /**
     * Get data historis untuk YoY calculation
     * GET /api/fact-historis/yoy-data?tahun=2024&bulan=3&metrik=pelanggan
     */
    public function getYoyData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tahun' => 'required|integer|min:2000|max:2100',
            'bulan' => 'required|integer|min:1|max:12',
            'metrik' => 'nullable|string|in:pelanggan,kwh_jual,rupiah',
        ]);

        $tahun = $validated['tahun'];
        $bulan = $validated['bulan'];
        $metrik = $validated['metrik'] ?? 'pelanggan';

        // Query menggunakan model FactHistoris Anda
        $data = FactHistoris::where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->where('metrik_kode', $metrik)
            ->where('is_akumulasi', false)       // Ambil data bulanan
            ->where('is_jumlah_tahunan', false)   // Bukan total tahunan
            ->get()
            ->pluck('nilai', 'golongan_kode')
            ->map(fn($val) => floatval($val))
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => $data,  // Format: { "S": 12500, "R": 450000, ... }
            'meta' => [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'metrik' => $metrik,
                'total' => array_sum($data),
            ]
        ]);
    }
}