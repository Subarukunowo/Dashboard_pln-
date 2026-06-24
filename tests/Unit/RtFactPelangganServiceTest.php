<?php

namespace Tests\Unit;

use App\Models\RtFactPelanggan;
use App\Repositories\RtFactPelangganRepository;
use App\Services\RtFactPelangganService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit test untuk RtFactPelangganService.
 *
 * Menggunakan SQLite in-memory (dikonfigurasi di phpunit.xml)
 * sehingga aman dijalankan tanpa database production.
 */
class RtFactPelangganServiceTest extends TestCase
{
    use RefreshDatabase;

    private RtFactPelangganService $service;
    private int $tahun;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(RtFactPelangganService::class);
        $this->tahun   = now()->year;
    }

    // ── getChartData ─────────────────────────────────────────────────────────

    /** getChartData mengembalikan structure yang benar */
    public function test_get_chart_data_returns_required_keys(): void
    {
        $result = $this->service->getChartData($this->tahun);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('tarif',            $result);
        $this->assertArrayHasKey('golongan',         $result);
        $this->assertArrayHasKey('rencanaData',      $result);
        $this->assertArrayHasKey('realisasiData',    $result);
        $this->assertArrayHasKey('targetGolAkum',    $result);
        $this->assertArrayHasKey('realisasiGolAkum', $result);
        $this->assertArrayHasKey('rkapData',         $result);
        $this->assertArrayHasKey('tahun_efektif',    $result);
    }

    /** getChartData fallback ke tahun lalu jika data tahun ini kosong */
    public function test_get_chart_data_fallback_ke_tahun_lalu(): void
    {
        $tahunLalu = $this->tahun - 1;

        // Insert data di tahun lalu saja
        RtFactPelanggan::create([
            'tahun'             => $tahunLalu,
            'bulan'             => 3,
            'tipe'              => 'realisasi',
            'sub_golongan_kode' => 'JUMLAH',
            'golongan_kode'     => 'TOTAL',
            'nilai_pelanggan'   => 1_000.0,
            'is_triwulan'       => false,
            'is_akumulasi'      => false,
            'sumber'            => 'manual',
        ]);

        $result = $this->service->getChartData($this->tahun);

        // Harus fallback ke tahun lalu
        $this->assertEquals($tahunLalu, $result['tahun_efektif']);
    }

    /** rencanaData berisi array 12 elemen untuk setiap tarif */
    public function test_rencana_data_has_12_months_per_tarif(): void
    {
        RtFactPelanggan::create([
            'tahun'             => $this->tahun,
            'bulan'             => 1,
            'tipe'              => 'target',
            'sub_golongan_kode' => 'JUMLAH',
            'golongan_kode'     => 'TOTAL',
            'nilai_pelanggan'   => 500.0,
            'is_triwulan'       => false,
            'is_akumulasi'      => false,
            'sumber'            => 'manual',
        ]);

        $result     = $this->service->getChartData($this->tahun);
        $rencana    = $result['rencanaData'];

        $this->assertArrayHasKey('JUMLAH', $rencana);
        $this->assertCount(12, $rencana['JUMLAH']);
    }

    /** Nilai realisasi muncul di index bulan yang benar (0-based) */
    public function test_realisasi_data_nilai_ada_di_index_bulan_yang_benar(): void
    {
        // Masukkan data bulan April (index 3)
        RtFactPelanggan::create([
            'tahun'             => $this->tahun,
            'bulan'             => 4, // April
            'tipe'              => 'realisasi',
            'sub_golongan_kode' => 'JUMLAH',
            'golongan_kode'     => 'TOTAL',
            'nilai_pelanggan'   => 12_345.0,
            'is_triwulan'       => false,
            'is_akumulasi'      => false,
            'sumber'            => 'manual',
        ]);

        $result    = $this->service->getChartData($this->tahun);
        $realisasi = $result['realisasiData']['JUMLAH'] ?? null;

        $this->assertNotNull($realisasi, 'realisasiData[JUMLAH] harus ada');
        $this->assertEquals(12_345.0, $realisasi[3], 'Index 3 (April) harus berisi 12345');
        $this->assertEquals(0.0, $realisasi[0], 'Index 0 (Januari) harus 0');
    }

    /** golongan berisi 6 entri (S,R,B,I,P,M) */
    public function test_golongan_contains_six_entries(): void
    {
        $result   = $this->service->getChartData($this->tahun);
        $golongan = $result['golongan'];

        $this->assertCount(6, $golongan);
        $this->assertStringContainsString('(S)', $golongan[0]);
        $this->assertStringContainsString('(R)', $golongan[1]);
    }

    /** rkapData berisi 7 entri (6 golongan + JUMLAH) */
    public function test_rkap_data_contains_seven_entries(): void
    {
        $result = $this->service->getChartData($this->tahun);
        $rkap   = $result['rkapData'];

        $this->assertCount(7, $rkap);

        $jumlah = collect($rkap)->firstWhere('golongan', 'JUMLAH');
        $this->assertNotNull($jumlah, 'rkapData harus mengandung JUMLAH');
    }

    // ── CRUD via service ─────────────────────────────────────────────────────

    /** create menyimpan record ke database */
    public function test_create_menyimpan_ke_database(): void
    {
        $data = [
            'tahun'             => $this->tahun,
            'bulan'             => 7,
            'tipe'              => 'target',
            'sub_golongan_kode' => 'JUMLAH',
            'golongan_kode'     => 'TOTAL',
            'nilai_pelanggan'   => 250.0,
            'is_triwulan'       => false,
            'is_akumulasi'      => false,
            'sumber'            => 'manual',
        ];

        $record = $this->service->create($data);

        $this->assertNotNull($record);
        $this->assertDatabaseHas('rt_fact_pelanggan', [
            'tahun' => $this->tahun,
            'bulan' => 7,
        ]);
    }

    /** update mengubah nilai dengan benar */
    public function test_update_mengubah_nilai_pelanggan(): void
    {
        $record = RtFactPelanggan::create([
            'tahun'             => $this->tahun,
            'bulan'             => 9,
            'tipe'              => 'realisasi',
            'sub_golongan_kode' => 'JUMLAH',
            'golongan_kode'     => 'TOTAL',
            'nilai_pelanggan'   => 100.0,
            'is_triwulan'       => false,
            'is_akumulasi'      => false,
            'sumber'            => 'manual',
        ]);

        $updated = $this->service->update($record->id, ['nilai_pelanggan' => 888.0]);

        $this->assertEquals(888.0, $updated->nilai_pelanggan);
        $this->assertDatabaseHas('rt_fact_pelanggan', [
            'id'              => $record->id,
            'nilai_pelanggan' => 888.0,
        ]);
    }

    /** delete menghapus record */
    public function test_delete_menghapus_record(): void
    {
        $record = RtFactPelanggan::create([
            'tahun'             => $this->tahun,
            'bulan'             => 10,
            'tipe'              => 'target',
            'sub_golongan_kode' => 'JUMLAH',
            'golongan_kode'     => 'TOTAL',
            'nilai_pelanggan'   => 50.0,
            'is_triwulan'       => false,
            'is_akumulasi'      => false,
            'sumber'            => 'manual',
        ]);

        $this->service->delete($record->id);

        $this->assertDatabaseMissing('rt_fact_pelanggan', ['id' => $record->id]);
    }

    /** getPaginated mengembalikan LengthAwarePaginator dengan filter */
    public function test_get_paginated_dengan_filter_tahun(): void
    {
        RtFactPelanggan::create([
            'tahun' => $this->tahun, 'bulan' => 1, 'tipe' => 'target',
            'sub_golongan_kode' => 'JUMLAH', 'golongan_kode' => 'TOTAL',
            'nilai_pelanggan' => 10.0, 'is_triwulan' => false,
            'is_akumulasi' => false, 'sumber' => 'manual',
        ]);
        RtFactPelanggan::create([
            'tahun' => $this->tahun - 1, 'bulan' => 1, 'tipe' => 'target',
            'sub_golongan_kode' => 'JUMLAH', 'golongan_kode' => 'TOTAL',
            'nilai_pelanggan' => 10.0, 'is_triwulan' => false,
            'is_akumulasi' => false, 'sumber' => 'manual',
        ]);

        $paginated = $this->service->getPaginated(15, ['tahun' => $this->tahun]);

        $this->assertEquals(1, $paginated->total());
    }
}
