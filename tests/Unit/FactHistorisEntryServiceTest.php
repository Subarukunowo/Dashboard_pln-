<?php

namespace Tests\Unit;

use App\Services\FactHistorisEntryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Unit test untuk FactHistorisEntryService.
 * Menguji business logic tanpa HTTP layer.
 */
class FactHistorisEntryServiceTest extends TestCase
{
    use RefreshDatabase;

    private FactHistorisEntryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(FactHistorisEntryService::class);
    }

    /** canEditPeriod true untuk bulan yang masih dalam deadline (≤ 1 bulan + 7 hari) */
    public function test_can_edit_period_true_saat_masih_dalam_deadline(): void
    {
        // Hari ini: 5 Februari 2026 → edit Januari 2026 masih boleh (deadline 7 Feb)
        Carbon::setTestNow(Carbon::create(2026, 2, 5));

        $result = $this->service->canEditPeriod(2026, 1);

        $this->assertTrue($result);

        Carbon::setTestNow();
    }

    /** canEditPeriod false jika deadline sudah lewat */
    public function test_can_edit_period_false_saat_deadline_lewat(): void
    {
        // Hari ini: 10 Februari 2026 → edit Januari 2026 sudah melewati deadline (7 Feb)
        Carbon::setTestNow(Carbon::create(2026, 2, 10));

        $result = $this->service->canEditPeriod(2026, 1);

        $this->assertFalse($result);

        Carbon::setTestNow();
    }

    /** canEditPeriod true untuk bulan berjalan */
    public function test_can_edit_period_true_untuk_bulan_berjalan(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 15));

        // Edit Maret 2026 (bulan berjalan) → selalu boleh
        $result = $this->service->canEditPeriod(2026, 3);

        $this->assertTrue($result);

        Carbon::setTestNow();
    }

    /** canEditPeriod false untuk periode masa depan */
    public function test_can_edit_period_false_untuk_periode_masa_depan(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 15));

        // Edit April 2026 (bulan depan) → tidak boleh
        $result = $this->service->canEditPeriod(2026, 4);

        $this->assertFalse($result);

        Carbon::setTestNow();
    }
}
