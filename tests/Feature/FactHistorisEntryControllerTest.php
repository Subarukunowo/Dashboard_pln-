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
        
        // ✅ FIX FINAL: Nonaktifkan SEMUA middleware untuk menghindari 419
        $this->withoutMiddleware();
        
        $this->seedLookupData();
    }

    protected function seedLookupData(): void
    {
        DimMetrik::create([
            'kode' => 'kwh_jual', 
            'nama' => 'KWH Jual', 
            'satuan' => 'KWH', 
            'kategori' => 'penjualan'
        ]);

        DimGolongan::create([
            'kode' => 'R1', 
            'nama' => 'Rumah Tangga', 
            'urutan' => 1,
            'alias' => 'RT'
        ]);
    }

    public function test_it_stores_data_successfully(): void
    {
        $this->mock(FactHistorisEntryService::class, function ($mock) {
            $mock->shouldReceive('canEditPeriod')->with(2026, 1)->andReturn(true);
        });

        $response = $this->postJson('/fact-historis', [
            'tahun' => 2026, 'bulan' => 1,
            'data'  => [['metrik_kode' => 'kwh_jual', 'golongan_kode' => 'R1', 'nilai' => 15000.50]]
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure(['success', 'message', 'created', 'updated']);
    }

    public function test_it_fails_when_data_field_is_missing(): void
    {
        $response = $this->postJson('/fact-historis', [
            'tahun'   => 2026, 'bulan' => 1,
            'entries' => [['metrik_kode' => 'kwh_jual', 'golongan_kode' => 'R1', 'nilai' => 1000]]
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['data'])
                 ->assertJsonFragment(['message' => 'The data field is required.']);
    }

    public function test_it_fails_when_deadline_is_exceeded(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 10, 10, 0, 0));
        $this->instance(FactHistorisEntryService::class, app(FactHistorisEntryService::class));

        $response = $this->postJson('/fact-historis', [
            'tahun' => 2026, 'bulan' => 1,
            'data'  => [['metrik_kode' => 'kwh_jual', 'golongan_kode' => 'R1', 'nilai' => 12000]]
        ]);

        $response->assertStatus(403)
                 ->assertJson(['success' => false])
                 ->assertJsonFragment(['message' => 'Periode ini sudah melewati batas waktu input.']);

        Carbon::setTestNow();
    }
}