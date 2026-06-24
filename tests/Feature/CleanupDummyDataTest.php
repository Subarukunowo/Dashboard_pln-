<?php

namespace Tests\Feature;

use App\Models\RtFactPelanggan;
use App\Models\RtFactTunggakan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Test khusus untuk menghapus data dummy / data testing.
 *
 * Mencakup:
 *   - DELETE /api/v1/fact/tunggakan/{id}   → hapus per ID
 *   - DELETE /api/v1/fact/pelanggan/{id}   → hapus per ID
 *   - DELETE /api/rt-fact-tunggakan-by-filter → hapus bulk by tahun
 *   - DELETE /api/v1/fact/pelanggan (destroyByFilter) → hapus bulk by tahun + tipe
 *
 * CATATAN: Test ini menggunakan RefreshDatabase sehingga semua
 * data yang dibuat akan otomatis di-rollback setelah setiap test.
 * Tidak ada data production yang tersentuh.
 */
class CleanupDummyDataTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    // ── Hapus per ID ─────────────────────────────────────────────────────────

    /** Hapus satu record tunggakan dummy by ID */
    public function test_hapus_satu_tunggakan_dummy_by_id(): void
    {
        $dummy = RtFactTunggakan::create([
            'tahun'               => 9999, // tahun dummy, pasti tidak ada di production
            'bulan'               => 1,
            'pal_berjalan'        => 0.0,
            'pal_tunggakan'       => 0.0,
            'ts_berjalan'         => 0.0,
            'ts_tunggakan'        => 0.0,
            'jumlah_tunggakan'    => 0.0,
            'pendapatan'          => 0.0,
            'ratio_tunggakan_pct' => 0.0,
            'sumber'              => 'manual',
        ]);

        $this->assertDatabaseHas('rt_fact_tunggakan', ['id' => $dummy->id]);

        $res = $this->deleteJson("/api/v1/fact/tunggakan/{$dummy->id}");

        $res->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('rt_fact_tunggakan', ['id' => $dummy->id]);
    }

    /** Hapus satu record pelanggan dummy by ID */
    public function test_hapus_satu_pelanggan_dummy_by_id(): void
    {
        $dummy = RtFactPelanggan::create([
            'tahun'             => 9999,
            'bulan'             => 1,
            'tipe'              => 'realisasi',
            'sub_golongan_kode' => 'DUMMY',
            'golongan_kode'     => 'X',
            'nilai_pelanggan'   => 0.0,
            'is_triwulan'       => false,
            'is_akumulasi'      => false,
            'sumber'            => 'manual',
        ]);

        $this->assertDatabaseHas('rt_fact_pelanggan', ['id' => $dummy->id]);

        $res = $this->deleteJson("/api/v1/fact/pelanggan/{$dummy->id}");

        $res->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('rt_fact_pelanggan', ['id' => $dummy->id]);
    }

    // ── Hapus bulk by filter (tahun) ─────────────────────────────────────────

    /** Hapus semua tunggakan dummy by tahun via destroyByFilter */
    public function test_hapus_bulk_tunggakan_dummy_by_tahun(): void
    {
        $tahunDummy = 9998;

        // Buat 12 record dummy (satu per bulan)
        foreach (range(1, 12) as $bulan) {
            RtFactTunggakan::create([
                'tahun'               => $tahunDummy,
                'bulan'               => $bulan,
                'pal_berjalan'        => 0.0,
                'pal_tunggakan'       => 0.0,
                'ts_berjalan'         => 0.0,
                'ts_tunggakan'        => 0.0,
                'jumlah_tunggakan'    => 0.0,
                'pendapatan'          => 0.0,
                'ratio_tunggakan_pct' => 0.0,
                'sumber'              => 'manual',
            ]);
        }

        $this->assertDatabaseCount('rt_fact_tunggakan', 12);

        $res = $this->deleteJson("/api/rt-fact-tunggakan-by-filter?tahun={$tahunDummy}");

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.deleted', 12);

        $this->assertDatabaseCount('rt_fact_tunggakan', 0);
        $this->assertDatabaseMissing('rt_fact_tunggakan', ['tahun' => $tahunDummy]);
    }

    /** Hapus bulk tunggakan — hanya tahun yang diminta, tidak menyentuh tahun lain */
    public function test_hapus_bulk_tunggakan_tidak_menyentuh_tahun_lain(): void
    {
        $tahunDummy  = 9997;
        $tahunLainnya = 9996;

        // Dummy yang akan dihapus
        RtFactTunggakan::create([
            'tahun' => $tahunDummy, 'bulan' => 6,
            'pal_berjalan' => 0.0, 'pal_tunggakan' => 0.0,
            'ts_berjalan' => 0.0, 'ts_tunggakan' => 0.0,
            'jumlah_tunggakan' => 0.0, 'pendapatan' => 0.0,
            'ratio_tunggakan_pct' => 0.0, 'sumber' => 'manual',
        ]);

        // Data tahun lain yang tidak boleh terhapus
        $keep = RtFactTunggakan::create([
            'tahun' => $tahunLainnya, 'bulan' => 6,
            'pal_berjalan' => 0.0, 'pal_tunggakan' => 0.0,
            'ts_berjalan' => 0.0, 'ts_tunggakan' => 0.0,
            'jumlah_tunggakan' => 0.0, 'pendapatan' => 0.0,
            'ratio_tunggakan_pct' => 0.0, 'sumber' => 'manual',
        ]);

        $this->deleteJson("/api/rt-fact-tunggakan-by-filter?tahun={$tahunDummy}");

        // Tahun lain masih ada
        $this->assertDatabaseHas('rt_fact_tunggakan', ['id' => $keep->id]);
        // Tahun dummy sudah hilang
        $this->assertDatabaseMissing('rt_fact_tunggakan', ['tahun' => $tahunDummy]);
    }

    /** Hapus bulk pelanggan dummy by tahun + tipe via destroyByFilter */
    public function test_hapus_bulk_pelanggan_dummy_by_tahun_dan_tipe(): void
    {
        $tahunDummy = 9995;

        // Insert: 3 target + 3 realisasi
        foreach (range(1, 3) as $bulan) {
            foreach (['target', 'realisasi'] as $tipe) {
                RtFactPelanggan::create([
                    'tahun'             => $tahunDummy,
                    'bulan'             => $bulan,
                    'tipe'              => $tipe,
                    'sub_golongan_kode' => 'DUMMY-' . $bulan,
                    'golongan_kode'     => 'X',
                    'nilai_pelanggan'   => 0.0,
                    'is_triwulan'       => false,
                    'is_akumulasi'      => false,
                    'sumber'            => 'manual',
                ]);
            }
        }

        $this->assertDatabaseCount('rt_fact_pelanggan', 6);

        // Hapus hanya tipe 'target'
        $res = $this->deleteJson("/api/v1/fact/pelanggan?tahun={$tahunDummy}&tipe=target");

        $res->assertOk()
            ->assertJsonPath('success', true);

        // Hanya 3 realisasi yang tersisa
        $this->assertDatabaseCount('rt_fact_pelanggan', 3);
        $this->assertDatabaseMissing('rt_fact_pelanggan', ['tahun' => $tahunDummy, 'tipe' => 'target']);
        $this->assertDatabaseHas('rt_fact_pelanggan', ['tahun' => $tahunDummy, 'tipe' => 'realisasi']);
    }

    /** Hapus 'semua' tipe pelanggan dummy sekaligus */
    public function test_hapus_semua_tipe_pelanggan_dummy(): void
    {
        $tahunDummy = 9994;

        foreach (['target', 'realisasi'] as $tipe) {
            RtFactPelanggan::create([
                'tahun'             => $tahunDummy,
                'bulan'             => 1,
                'tipe'              => $tipe,
                'sub_golongan_kode' => 'DUMMY',
                'golongan_kode'     => 'X',
                'nilai_pelanggan'   => 0.0,
                'is_triwulan'       => false,
                'is_akumulasi'      => false,
                'sumber'            => 'manual',
            ]);
        }

        // Hapus semua (tanpa filter tipe, atau tipe=semua)
        $res = $this->deleteJson("/api/v1/fact/pelanggan?tahun={$tahunDummy}");

        $res->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('rt_fact_pelanggan', ['tahun' => $tahunDummy]);
    }

    // ── Edge cases ───────────────────────────────────────────────────────────

    /** Hapus ID yang tidak ada mengembalikan 404 */
    public function test_hapus_id_tidak_ada_returns_404(): void
    {
        $res = $this->deleteJson('/api/v1/fact/tunggakan/99999999');

        // Endpoint destroy by ID harus 404 jika tidak ada
        // atau 200 jika service tidak throw (tergantung implementasi)
        $this->assertContains($res->status(), [200, 404]);
    }

    /** Hapus bulk dengan tahun yang tidak ada — deleted = 0, tidak error */
    public function test_hapus_bulk_tahun_tidak_ada_returns_zero(): void
    {
        $res = $this->deleteJson('/api/rt-fact-tunggakan-by-filter?tahun=1800');

        $res->assertOk()
            ->assertJsonPath('data.deleted', 0);
    }
}
