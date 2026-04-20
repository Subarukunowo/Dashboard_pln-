<?php

namespace App\Services;

use App\Models\FactHistoris;
use Carbon\Carbon;

class FactHistorisEntryService
{
    /**
     * Cek apakah periode masih bisa diedit
     * Aturan: Bisa edit sampai tanggal 7 bulan berikutnya
     */
    public function canEditPeriod(int $tahun, int $bulan): bool
    {
        // Tahun < 2026 tidak bisa diedit melalui fitur ini
        if ($tahun < 2026) {
            return false;
        }
        
        $now = Carbon::now();
        $currentYear = $now->year;
        $currentMonth = $now->month;
        $currentDay = $now->day;

        // Hitung deadline: tanggal 7 bulan berikutnya
        $targetDate = Carbon::createFromDate($tahun, $bulan, 1);
        $deadline = $targetDate->copy()->addMonth()->day(7);

        // Jika sudah melewati deadline, tidak bisa edit
        if ($now > $deadline) {
            return false;
        }

        return true;
    }

    /**
     * Dapatkan deadline edit untuk periode tertentu
     */
    public function getEditDeadline(int $tahun, int $bulan): Carbon
    {
        return Carbon::createFromDate($tahun, $bulan, 1)
            ->addMonth()
            ->day(7)
            ->endOfDay();
    }

    /**
     * Cek apakah tahun sudah lengkap semua bulannya
     */
    public function isYearComplete(int $tahun): bool
    {
        $metrikCount = \App\Models\DimMetrik::count();
        $golonganCount = \App\Models\DimGolongan::count();
        $requiredRecords = $metrikCount * $golonganCount * 12; // 12 bulan

        $actualCount = FactHistoris::where('tahun', $tahun)
            ->where('is_akumulasi', 0)
            ->where('is_jumlah_tahunan', 0)
            ->count();

        return $actualCount >= $requiredRecords;
    }

    /**
     * Dapatkan tahun-tahun yang belum ada data
     */
    public function getAvailableYears(int $startYear = 2026, int $yearsAhead = 2): \Illuminate\Support\Collection
    {
        $tahunDenganData = FactHistoris::where('is_akumulasi', 0)
            ->where('is_jumlah_tahunan', 0)
            ->distinct()
            ->pluck('tahun')
            ->toArray();

        $tahunAkhir = now()->year + $yearsAhead;
        
        return collect(range($startYear, $tahunAkhir))
            ->filter(fn($t) => !in_array($t, $tahunDenganData))
            ->values();
    }
}