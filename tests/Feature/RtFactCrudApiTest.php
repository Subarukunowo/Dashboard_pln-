<?php

namespace Tests\Feature;

use App\Models\RtFactPelanggan;
use App\Models\RtFactTunggakan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Feature test untuk CRUD API endpoints dashboard realtime.
 *
 * Mencakup:
 *   - POST   /api/v1/fact/pelanggan   → store
 *   - GET    /api/v1/fact/pelanggan   → index (paginated)
 *   - GET    /api/v1/fact/pelanggan/{id} → show
 *   - PUT    /api/v1/fact/pelanggan/{id} → update
 *   - DELETE /api/v1/fact/pelanggan/{id} → destroy
 *   - DELETE /api/v1/fact/tunggakan/{id} → destroy
 *
 * Semua endpoint v1 butuh auth:sanctum.
 */
class RtFactCrudApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private int  $tahun;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tahun = now()->year;

        // Buat user dan autentikasi via Sanctum
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    // ── POST /api/v1/fact/pelanggan ──────────────────────────────────────────

    /** Store record pelanggan baru */
    public function test_store_pelanggan_creates_record(): void
    {
        $payload = [
            'tahun'             => $this->tahun,
            'bulan'             => 4,
            'tipe'              => 'realisasi',
            'sub_golongan_kode' => 'JUMLAH',
            'golongan_kode'     => 'TOTAL',
            'nilai_pelanggan'   => 500_000,
            'is_triwulan'       => false,
            'is_akumulasi'      => false,
            'sumber'            => 'manual',
        ];

        $res = $this->postJson('/api/v1/fact/pelanggan', $payload);

        $res->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.nilai_pelanggan', 500_000); // ← tanpa .0

        $this->assertDatabaseHas('rt_fact_pelanggan', [
            'tahun'             => $this->tahun,
            'bulan'             => 4,
            'sub_golongan_kode' => 'JUMLAH',
        ]);
    }

    /** Validasi: field wajib tidak boleh kosong */
    public function test_store_pelanggan_fails_validation_without_required_fields(): void
    {
        $res = $this->postJson('/api/v1/fact/pelanggan', [
            'bulan' => 4,
            // tahun, tipe, sub_golongan_kode, golongan_kode hilang
        ]);

        $res->assertStatus(422)
            ->assertJsonValidationErrors(['tahun', 'tipe', 'sub_golongan_kode', 'golongan_kode']);
    }

    // ── GET /api/v1/fact/pelanggan ───────────────────────────────────────────

    /** Index mengembalikan data terpaginasi */
    public function test_index_pelanggan_returns_paginated_data(): void
    {
        RtFactPelanggan::create([
            'tahun' => $this->tahun, 'bulan' => 1, 'tipe' => 'target',
            'sub_golongan_kode' => 'JUMLAH', 'golongan_kode' => 'TOTAL',
            'nilai_pelanggan' => 100.0, 'is_triwulan' => false,
            'is_akumulasi' => false, 'sumber' => 'manual',
        ]);

        $res = $this->getJson("/api/v1/fact/pelanggan?tahun={$this->tahun}&per_page=10");

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['data', 'total', 'per_page', 'current_page']]);

        $this->assertGreaterThanOrEqual(1, $res->json('data.total'));
    }

    /** Filter by tipe bekerja dengan benar */
    public function test_index_pelanggan_filters_by_tipe(): void
    {
        RtFactPelanggan::create([
            'tahun' => $this->tahun, 'bulan' => 2, 'tipe' => 'target',
            'sub_golongan_kode' => 'JUMLAH', 'golongan_kode' => 'TOTAL',
            'nilai_pelanggan' => 200.0, 'is_triwulan' => false,
            'is_akumulasi' => false, 'sumber' => 'manual',
        ]);
        RtFactPelanggan::create([
            'tahun' => $this->tahun, 'bulan' => 2, 'tipe' => 'realisasi',
            'sub_golongan_kode' => 'JUMLAH-R', 'golongan_kode' => 'R',
            'nilai_pelanggan' => 150.0, 'is_triwulan' => false,
            'is_akumulasi' => false, 'sumber' => 'manual',
        ]);

        $res = $this->getJson("/api/v1/fact/pelanggan?tahun={$this->tahun}&tipe=target");

        $res->assertOk();

        $items = $res->json('data.data');
        $this->assertNotEmpty($items);
        foreach ($items as $item) {
            $this->assertEquals('target', $item['tipe']);
        }
    }

    // ── GET /api/v1/fact/pelanggan/{id} ─────────────────────────────────────

    /** Show mengembalikan record yang tepat */
    public function test_show_pelanggan_returns_correct_record(): void
    {
        $record = RtFactPelanggan::create([
            'tahun' => $this->tahun, 'bulan' => 5, 'tipe' => 'realisasi',
            'sub_golongan_kode' => 'JUMLAH', 'golongan_kode' => 'TOTAL',
            'nilai_pelanggan' => 777.0, 'is_triwulan' => false,
            'is_akumulasi' => false, 'sumber' => 'manual',
        ]);

        $res = $this->getJson("/api/v1/fact/pelanggan/{$record->id}");

        $res->assertOk()
            ->assertJsonPath('data.id', $record->id)
            ->assertJsonPath('data.nilai_pelanggan', 777); // ← tanpa .0
    }

    /** Show mengembalikan 404 jika ID tidak ada */
    public function test_show_pelanggan_returns_404_for_unknown_id(): void
    {
        $res = $this->getJson('/api/v1/fact/pelanggan/99999');

        $res->assertNotFound()
            ->assertJsonPath('success', false);
    }

    // ── PUT /api/v1/fact/pelanggan/{id} ─────────────────────────────────────

    /** Update mengubah nilai_pelanggan dengan benar */
    public function test_update_pelanggan_changes_nilai(): void
    {
        $record = RtFactPelanggan::create([
            'tahun' => $this->tahun, 'bulan' => 7, 'tipe' => 'target',
            'sub_golongan_kode' => 'JUMLAH', 'golongan_kode' => 'TOTAL',
            'nilai_pelanggan' => 100.0, 'is_triwulan' => false,
            'is_akumulasi' => false, 'sumber' => 'manual',
        ]);

        $res = $this->putJson("/api/v1/fact/pelanggan/{$record->id}", [
            'nilai_pelanggan' => 999.0,
        ]);

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.nilai_pelanggan', 999); // ← tanpa .0

        $this->assertDatabaseHas('rt_fact_pelanggan', [
            'id'              => $record->id,
            'nilai_pelanggan' => 999.0,
        ]);
    }

    // ── DELETE /api/v1/fact/pelanggan/{id} ───────────────────────────────────

    /** Delete menghapus record dari database */
    public function test_destroy_pelanggan_removes_record(): void
    {
        $record = RtFactPelanggan::create([
            'tahun' => $this->tahun, 'bulan' => 8, 'tipe' => 'realisasi',
            'sub_golongan_kode' => 'JUMLAH', 'golongan_kode' => 'TOTAL',
            'nilai_pelanggan' => 400.0, 'is_triwulan' => false,
            'is_akumulasi' => false, 'sumber' => 'manual',
        ]);

        $res = $this->deleteJson("/api/v1/fact/pelanggan/{$record->id}");

        $res->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('rt_fact_pelanggan', ['id' => $record->id]);
    }

    /** Akses tanpa auth ditolak */
    public function test_crud_endpoints_require_authentication(): void
    {
        // Logout dari Sanctum
        $this->app['auth']->forgetGuards();

        $res = $this->postJson('/api/v1/fact/pelanggan', [
            'tahun' => $this->tahun, 'bulan' => 1, 'tipe' => 'target',
            'sub_golongan_kode' => 'JUMLAH', 'golongan_kode' => 'TOTAL',
            'nilai_pelanggan' => 100.0, 'is_triwulan' => false,
            'is_akumulasi' => false, 'sumber' => 'manual',
        ]);

        $res->assertStatus(401);
    }
}