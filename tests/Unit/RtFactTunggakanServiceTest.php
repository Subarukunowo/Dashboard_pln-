<?php

namespace Tests\Unit;

use App\Models\RtFactTunggakan;
use App\Services\RtFactTunggakanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit test untuk RtFactTunggakanService.
 */
class RtFactTunggakanServiceTest extends TestCase
{
    use RefreshDatabase;

    private RtFactTunggakanService $service;
    private int $tahun;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(RtFactTunggakanService::class);
        $this->tahun   = now()->year;
    }

    // ── getChartData ─────────────────────────────────────────────────────────

    /** getChartData mengembalikan structure yang benar */
    public function test_get_chart_data_returns_required_keys(): void
    {
        $result = $this->service->getChartData($this->tahun);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data',          $result);
        $this->assertArrayHasKey('tahun_efektif', $result);
        $this->assertArrayHasKey('is_fallback',   $result);
    }

    /** getChartData mengembalikan data kosong (array) jika tidak ada records */
    public function test_get_chart_data_empty_when_no_records(): void
    {
        $result = $this->service->getChartData($this->tahun);

        // Fallback ke tahun lalu juga kosong → data = []
        $this->assertIsArray($result['data']);
    }

    /** getChartData fallback ke tahun lalu jika tahun ini kosong */
    public function test_get_chart_data_fallback_ke_tahun_lalu(): void
    {
        $tahunLalu = $this->tahun - 1;

        RtFactTunggakan::create([
            'tahun'               => $tahunLalu,
            'bulan'               => 12,
            'pal_berjalan'        => 100.0,
            'pal_tunggakan'       => 5.0,
            'ts_berjalan'         => 50.0,
            'ts_tunggakan'        => 2.0,
            'jumlah_tunggakan'    => 7.0,
            'pendapatan'          => 999_000.0,
            'ratio_tunggakan_pct' => 0.007,
            'sumber'              => 'manual',
        ]);

        $result = $this->service->getChartData($this->tahun);

        $this->assertTrue($result['is_fallback']);
        $this->assertEquals($tahunLalu, $result['tahun_efektif']);
        $this->assertCount(1, $result['data']);
    }

    /** Tidak ada fallback jika data tahun ini ada */
    public function test_get_chart_data_no_fallback_when_data_exists(): void
    {
        RtFactTunggakan::create([
            'tahun'               => $this->tahun,
            'bulan'               => 1,
            'pal_berjalan'        => 100.0,
            'pal_tunggakan'       => 3.0,
            'ts_berjalan'         => 40.0,
            'ts_tunggakan'        => 1.5,
            'jumlah_tunggakan'    => 4.5,
            'pendapatan'          => 800_000.0,
            'ratio_tunggakan_pct' => 0.005625,
            'sumber'              => 'manual',
        ]);

        $result = $this->service->getChartData($this->tahun);

        $this->assertFalse($result['is_fallback']);
        $this->assertEquals($this->tahun, $result['tahun_efektif']);
    }

    /** Data row punya semua field yang dibutuhkan frontend */
    public function test_chart_data_row_has_all_required_frontend_fields(): void
    {
        RtFactTunggakan::create([
            'tahun'               => $this->tahun,
            'bulan'               => 6,
            'pal_berjalan'        => 200.0,
            'pal_tunggakan'       => 10.0,
            'ts_berjalan'         => 80.0,
            'ts_tunggakan'        => 4.0,
            'jumlah_tunggakan'    => 14.0,
            'pendapatan'          => 1_500_000.0,
            'ratio_tunggakan_pct' => 0.0093,
            'sumber'              => 'manual',
        ]);

        $result = $this->service->getChartData($this->tahun);
        $row    = $result['data'][0];

        // Field yang dipakai frontend
        $this->assertArrayHasKey('id',              $row);
        $this->assertArrayHasKey('bulan',           $row);
        $this->assertArrayHasKey('palBerjalan',     $row);
        $this->assertArrayHasKey('palTunggakan',    $row);
        $this->assertArrayHasKey('tsBerjalan',      $row);
        $this->assertArrayHasKey('tsTunggakan',     $row);
        $this->assertArrayHasKey('jumlahTunggakan', $row);
        $this->assertArrayHasKey('pendapatan',      $row);
        $this->assertArrayHasKey('ratioTunggakan',  $row);
        $this->assertArrayHasKey('ratioTarget',     $row);
    }

    /** ratioTunggakan dikonversi dari pct ke desimal (dibagi 100) */
    public function test_ratio_tunggakan_dikonversi_ke_desimal(): void
    {
        RtFactTunggakan::create([
            'tahun'               => $this->tahun,
            'bulan'               => 3,
            'pal_berjalan'        => 0.0,
            'pal_tunggakan'       => 0.0,
            'ts_berjalan'         => 0.0,
            'ts_tunggakan'        => 0.0,
            'jumlah_tunggakan'    => 0.0,
            'pendapatan'          => 500_000.0,
            'ratio_tunggakan_pct' => 0.5, // 0.5% tersimpan di DB
            'sumber'              => 'manual',
        ]);

        $result = $this->service->getChartData($this->tahun);
        $row    = $result['data'][0];

        // 0.5 / 100 = 0.005 di frontend
        $this->assertEquals(0.005, round($row['ratioTunggakan'], 4));
    }

    /** ratioTarget adalah konstanta 0.0001 (0.01%) */
    public function test_ratio_target_adalah_konstanta(): void
    {
        RtFactTunggakan::create([
            'tahun' => $this->tahun, 'bulan' => 1,
            'pal_berjalan' => 0.0, 'pal_tunggakan' => 0.0,
            'ts_berjalan' => 0.0, 'ts_tunggakan' => 0.0,
            'jumlah_tunggakan' => 0.0, 'pendapatan' => 0.0,
            'ratio_tunggakan_pct' => 0.0, 'sumber' => 'manual',
        ]);

        $result = $this->service->getChartData($this->tahun);
        $row    = $result['data'][0];

        $this->assertEquals(RtFactTunggakanService::RATIO_TARGET, $row['ratioTarget']);
    }

    /** Data diurutkan by bulan ascending */
    public function test_data_diurutkan_by_bulan_ascending(): void
    {
        foreach ([6, 2, 9] as $bulan) {
            RtFactTunggakan::create([
                'tahun' => $this->tahun, 'bulan' => $bulan,
                'pal_berjalan' => 0.0, 'pal_tunggakan' => 0.0,
                'ts_berjalan' => 0.0, 'ts_tunggakan' => 0.0,
                'jumlah_tunggakan' => 0.0, 'pendapatan' => 0.0,
                'ratio_tunggakan_pct' => 0.0, 'sumber' => 'manual',
            ]);
        }

        $result = $this->service->getChartData($this->tahun);
        $bulans = collect($result['data'])->pluck('bulan')->toArray();

        // Harus berurutan: FEBRUARI, JUNI, SEPTEMBER
        $this->assertEquals(['FEBRUARI', 'JUNI', 'SEPTEMBER'], $bulans);
    }

    // ── CRUD via service ─────────────────────────────────────────────────────

    /** create menyimpan ke database */
    public function test_create_menyimpan_ke_database(): void
    {
        $record = $this->service->create([
            'tahun'               => $this->tahun,
            'bulan'               => 5,
            'pal_berjalan'        => 100.0,
            'pal_tunggakan'       => 5.0,
            'ts_berjalan'         => 60.0,
            'ts_tunggakan'        => 3.0,
            'jumlah_tunggakan'    => 8.0,
            'pendapatan'          => 750_000.0,
            'ratio_tunggakan_pct' => 0.0107,
            'sumber'              => 'manual',
        ]);

        $this->assertNotNull($record);
        $this->assertDatabaseHas('rt_fact_tunggakan', [
            'tahun' => $this->tahun,
            'bulan' => 5,
        ]);
    }

    /** update mengubah pendapatan */
    public function test_update_mengubah_pendapatan(): void
    {
        $record = RtFactTunggakan::create([
            'tahun'               => $this->tahun,
            'bulan'               => 8,
            'pal_berjalan'        => 0.0,
            'pal_tunggakan'       => 0.0,
            'ts_berjalan'         => 0.0,
            'ts_tunggakan'        => 0.0,
            'jumlah_tunggakan'    => 0.0,
            'pendapatan'          => 100_000.0,
            'ratio_tunggakan_pct' => 0.0,
            'sumber'              => 'manual',
        ]);

        $updated = $this->service->update($record->id, ['pendapatan' => 200_000.0]);

        $this->assertEquals(200_000.0, $updated->pendapatan);
    }

    /** delete menghapus record dari database */
    public function test_delete_menghapus_record(): void
    {
        $record = RtFactTunggakan::create([
            'tahun'               => $this->tahun,
            'bulan'               => 11,
            'pal_berjalan'        => 0.0,
            'pal_tunggakan'       => 0.0,
            'ts_berjalan'         => 0.0,
            'ts_tunggakan'        => 0.0,
            'jumlah_tunggakan'    => 0.0,
            'pendapatan'          => 0.0,
            'ratio_tunggakan_pct' => 0.0,
            'sumber'              => 'manual',
        ]);

        $this->service->delete($record->id);

        $this->assertDatabaseMissing('rt_fact_tunggakan', ['id' => $record->id]);
    }
}
