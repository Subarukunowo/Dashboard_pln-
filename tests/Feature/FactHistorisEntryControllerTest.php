<?php

namespace Tests\Feature;

use App\Models\DimGolongan;
use App\Models\DimMetrik;
use App\Services\FactHistorisEntryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FactHistorisEntryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedLookupData();
    }

    protected function seedLookupData(): void
    {
        // Seed data lookup agar validasi 'exists:dim_metrik' & 'exists:dim_golongan' lolos
        DimMetrik::create(['kode' => 'kwh_jual', 'nama' => 'KWH Jual', 'satuan' => 'KWH', 'kategori' => 'penjualan']);
        DimGolongan::create(['kode' => 'R1', 'nama' => 'Rumah Tangga', 'urutan' => 1]);
    }

    /**
     * ✅ SUCCESS: Data valid & deadline belum lewat
     */
    public function test_it_stores_data_successfully(): void
    {
        // Mock service agar lolos pengecekan deadline
        $this->mock(FactHistorisEntryService::class, function ($mock) {
            $mock->shouldReceive('canEditPeriod')->with(2026, 1)->andReturn(true);
        });

        $response = $this->postJson('/fact-historis', [
            'tahun'  => 2026,
            'bulan'  => 1,
            'data'   => [
                ['metrik_kode' => 'kwh_jual', 'golongan_kode' => 'R1', 'nilai' => 15000.50]
            ]
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure(['success', 'message', 'created', 'updated']);

        $this->assertDatabaseHas('fact_historis', [
            'tahun' => 2026, 'bulan' => 1, 'metrik_kode' => 'kwh_jual', 'golongan_kode' => 'R1', 'nilai' => 15000.50
        ]);
    }

    /**
     * ❌ FAILURE 1: Validasi gagal karena key `data` hilang/typo
     */
    public function test_it_fails_when_data_field_is_missing(): void
    {
        // Tidak perlu mock service, validasi Laravel gagal sebelum controller dijalankan
        $response = $this->postJson('/fact-historis', [
            'tahun'   => 2026,
            'bulan'   => 1,
            'entries' => [ // ❌ Key salah, controller expect 'data'
                ['metrik_kode' => 'kwh_jual', 'golongan_kode' => 'R1', 'nilai' => 1000]
            ]
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['data'])
                 ->assertJsonFragment(['message' => 'The data field is required.']);

        $this->assertDatabaseCount('fact_historis', 0);
    }

    /**
     * ❌ FAILURE 2: Deadline 1 bulan + 7 hari sudah lewat
     */
    public function test_it_fails_when_deadline_1_month_plus_7_days_is_exceeded(): void
    {
        // 🕒 Mock waktu: Sekarang 10 Februari 2026
        // Deadline Januari 2026 = 7 Februari 2026 → SUDAH LEWAT
        Carbon::setTestNow(Carbon::create(2026, 2, 10, 10, 0, 0));

        // Gunakan service ASLI (tidak di-mock) agar logika `canEditPeriod` benar-benar dijalankan
        $this->instance(FactHistorisEntryService::class, app(FactHistorisEntryService::class));

        $response = $this->postJson('/fact-historis', [
            'tahun'  => 2026,
            'bulan'  => 1,
            'data'   => [
                ['metrik_kode' => 'kwh_jual', 'golongan_kode' => 'R1', 'nilai' => 12000]
            ]
        ]);

        $response->assertStatus(403)
                 ->assertJson(['success' => false])
                 ->assertJsonFragment(['message' => 'Periode ini sudah melewati batas waktu input.']);

        // ⚠️ WAJIB: Reset waktu mock agar tidak merusak test lain
        Carbon::setTestNow();
    }
}