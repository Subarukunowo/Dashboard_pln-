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
    // =========================================================
    // LOCK LOGIC
    // =========================================================

    /**
     * Cek apakah periode masih bisa diedit.
     *
     * Aturan:
     * - Tahun < 2026 = TIDAK bisa diedit (data historis read-only)
     * - Periode berjalan atau masa depan = bisa diedit sampai deadline
     * - Deadline = tanggal 7 bulan BERIKUTNYA pukul 23:59:59
     *
     * Contoh: April 2026 → deadline = 7 Mei 2026 23:59:59
     * Jika sekarang 22 April 2026 → MASIH bisa edit ✓
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
     * Deadline edit = tanggal 7 bulan berikutnya pukul 23:59:59
     */
    public function getEditDeadline(int $tahun, int $bulan): Carbon
    {
        return Carbon::createFromDate($tahun, $bulan, 1)
            ->addMonthNoOverflow()
            ->setDay(7)
            ->endOfDay();
    }

    /**
     * Pesan informatif tentang status periode
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
        $bulanNama  = $now->setMonth($bulan)->isoFormat('MMMM');

        return [
            'can_edit' => $canEdit,
            'reason'   => $canEdit
                ? "Dapat diedit sampai {$deadline->translatedFormat('d F Y')}"
                : "Periode {$bulanNama} {$tahun} sudah terkunci sejak {$deadline->translatedFormat('d F Y')}.",
            'deadline' => $deadline->toDateTimeString(),
        ];
    }

    // =========================================================
    // AVAILABLE YEARS
    // =========================================================

    /**
     * Dapatkan tahun yang TERSEDIA untuk input.
     *
     * Bug sebelumnya: tahun dihapus dari daftar begitu ada SATU bulan tersimpan.
     * Fix: tahun tetap tersedia selama masih ada bulan yang bisa diedit.
     *
     * Logika:
     * - Tahun mulai dari 2026
     * - Tersedia = ada minimal 1 bulan di tahun tsb yang belum lewat deadline
     * - Atau tahun tersebut belum ada data sama sekali
     */
    public function getAvailableYears(int $startYear = 2026, int $yearsAhead = 2): Collection
    {
        $now     = Carbon::now();
        $endYear = $now->year + $yearsAhead;

        return collect(range($startYear, $endYear))
            ->filter(function (int $tahun) use ($now) {
                // Selalu tersedia jika ada bulan yang masih bisa diedit
                for ($bulan = 1; $bulan <= 12; $bulan++) {
                    if ($this->canEditPeriod($tahun, $bulan)) {
                        return true;
                    }
                }
                return false;
            })
            ->values();
    }

    // =========================================================
    // DIM_PERIODE AUTO-REGISTER
    // =========================================================

    /**
     * Pastikan tahun ada di dim_periode.
     * Dipanggil setelah store data berhasil.
     *
     * Jika tahun belum ada di dim_periode, buat row baru dengan:
     * - granularitas = 'bulanan' (data entry selalu bulanan)
     * - metrik_tersedia = daftar metrik dari data yang disimpan
     */
    public function ensureDimPeriode(int $tahun, array $metrikKodes = []): DimPeriode
    {
        // Ambil metrik yang ada di DB untuk tahun ini
        if (empty($metrikKodes)) {
            $metrikKodes = FactHistoris::where('tahun', $tahun)
                ->where('is_akumulasi', 0)
                ->whereRaw('LOWER(metrik_kode) IN (?)', [
                    implode(',', ['kwh_jual','mwh_jual','rupiah','pelanggan','daya_va'])
                ])
                ->distinct()
                ->pluck('metrik_kode')
                ->map(fn($k) => strtolower($k))
                ->unique()
                ->values()
                ->toArray();
        }

        // Upsert dim_periode
        $periode = DimPeriode::firstOrNew(['tahun' => $tahun]);

        // Update metrik_tersedia: gabungkan yang sudah ada + yang baru
        $existing = [];
        if ($periode->metrik_tersedia) {
            $decoded = json_decode($periode->metrik_tersedia, true);
            $existing = is_array($decoded) ? $decoded : [];
        }

        $merged = array_values(array_unique(array_merge($existing, $metrikKodes)));

        $periode->granularitas    = 'bulanan';
        $periode->metrik_tersedia = json_encode($merged);
        $periode->save();

        return $periode;
    }

    // =========================================================
    // UTIL
    // =========================================================

    public function isYearComplete(int $tahun): bool
    {
        $metrikCount   = DimMetrik::count();
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