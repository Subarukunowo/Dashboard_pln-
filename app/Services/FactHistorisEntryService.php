<?php

namespace App\Services;

use App\Models\DimGolongan;
use App\Models\DimMetrik;
use App\Models\DimPeriode;
use App\Models\FactHistoris;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FactHistorisEntryService
{
    // Daftar metrik lengkap yang wajib tersedia untuk input
    private const COMPLETE_METRICS = [
        'kwh_jual',
        'rupiah',
        'pelanggan',
        'daya_va',
        'rpkwh',
        'pertumbuhan_yoy'
    ];

    // =========================================================
    // LOCK LOGIC - PER BULAN (Poin 1)
    // =========================================================

    /**
     * Cek apakah periode bulan tertentu masih bisa diedit.
     * Aturan: Edit tersedia sampai tanggal 7 bulan berikutnya pukul 23:59:59.
     */
    public function canEditPeriod(int $tahun, int $bulan): bool
    {
        if ($tahun < 2026) {
            return false;
        }

        $deadline = $this->getEditDeadline($tahun, $bulan);
        return Carbon::now()->lte($deadline);
    }

    /**
     * Hitung deadline edit: tanggal 7 bulan berikutnya, akhir hari.
     */
    public function getEditDeadline(int $tahun, int $bulan): Carbon
    {
        return Carbon::createFromDate($tahun, $bulan, 1)
            ->addMonthNoOverflow()
            ->setDay(7)
            ->endOfDay();
    }

    /**
     * Dapatkan informasi status periode untuk ditampilkan ke user.
     */
    public function getPeriodeStatus(int $tahun, int $bulan): array
    {
        if ($tahun < 2026) {
            return [
                'can_edit' => false,
                'reason'   => 'Data historis 1997–2025 hanya bisa dibaca.',
                'deadline' => null,
            ];
        }

        $deadline   = $this->getEditDeadline($tahun, $bulan);
        $now        = Carbon::now();
        $canEdit    = $now->lte($deadline);
        $bulanNama  = Carbon::create()->month($bulan)->isoFormat('MMMM');

        return [
            'can_edit' => $canEdit,
            'reason'   => $canEdit
                ? "Dapat diedit sampai {$deadline->translatedFormat('d F Y')}"
                : "Periode {$bulanNama} {$tahun} sudah terkunci sejak {$deadline->translatedFormat('d F Y')}.",
            'deadline' => $deadline->toDateTimeString(),
        ];
    }

    // =========================================================
    // YEAR AVAILABILITY LOGIC - PER TAHUN (Poin 2)
    // =========================================================

    /**
     * Cek apakah sebuah tahun masih tersedia untuk input.
     * 
     * Logika (Poin 2):
     * Tahun TIDAK dikunci selama masih ada minimal 1 bulan yang belum lewat deadline.
     * Tahun hanya akan hilang dari dropdown jika Bulan 1-12 SEMUANYA sudah lewat tanggal 7 bulan berikutnya.
     */
    public function isYearEditable(int $tahun): bool
    {
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            if ($this->canEditPeriod($tahun, $bulan)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Dapatkan daftar tahun yang tersedia untuk dropdown.
     */
    public function getAvailableYears(int $startYear = 2026, int $yearsAhead = 2): Collection
    {
        $now     = now();
        $endYear = $now->year + $yearsAhead;

        return collect(range($startYear, $endYear))
            ->filter(fn($tahun) => $this->isYearEditable($tahun))
            ->values();
    }

    // =========================================================
    // DIM_PERIODE MANAGEMENT (Poin 3)
    // =========================================================

    /**
     * Pastikan entri dim_periode ada dan terisi dengan daftar metrik LENGKAP.
     * 
     * Poin 3: Selalu simpan metrik_tersedia dengan daftar lengkap,
     * bukan hanya metrik yang diinput saat itu.
     */
    public function ensureDimPeriode(int $tahun): DimPeriode
    {
        $periode = DimPeriode::firstOrNew(['tahun' => $tahun]);

        $periode->granularitas    = 'bulanan';
        $periode->metrik_tersedia = json_encode(self::COMPLETE_METRICS);
        $periode->save();

        return $periode;
    }

    // =========================================================
    // UTILITIES
    // =========================================================

    public function isYearComplete(int $tahun): bool
    {
        $metrikCount   = DimMetrik::whereIn('kode', self::COMPLETE_METRICS)->count();
        $golonganCount = DimGolongan::count();
        $required      = $metrikCount * $golonganCount * 12;

        $actual = FactHistoris::where('tahun', $tahun)
            ->where('is_akumulasi', 0)
            ->where('is_jumlah_tahunan', 0)
            ->where('is_triwulan', 0)
            ->where('bulan', '>', 0)
            ->count();

        return $actual >= $required;
    }
}