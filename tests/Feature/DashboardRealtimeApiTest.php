<?php

namespace Tests\Feature;

use App\Models\RtFactPelanggan;
use App\Models\RtFactTunggakan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature test untuk semua endpoint API chart dashboard realtime.
 *
 * Endpoint yang diuji (semua public, tanpa auth):
 *   GET /api/chart/pelanggan
 *   GET /api/chart/tunggakan
 *   GET /api/chart/penjualan
 *   GET /api/chart/losses
 *   GET /api/chart/bpp
 *   GET /api/chart/hjr
 *   GET /api/chart/jam-nyala
 *   GET /api/chart/p2tl
 *   GET /api/chart/pendapatan
 *   GET /api/chart/rpkwh
 *   GET /api/chart/babin
 */
class DashboardRealtimeApiTest extends TestCase
{
    use RefreshDatabase;

    private int $tahun;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tahun = now()->year;
    }

    // ── /api/chart/pelanggan ─────────────────────────────────────────────────

    /** Endpoint pelanggan mengembalikan structure yang benar dengan data kosong */
    public function test_chart_pelanggan_returns_valid_structure(): void
    {
        $res = $this->getJson("/api/chart/pelanggan?tahun={$this->tahun}");

        $res->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'tarif',
                    'golongan',
                    'rencanaData',
                    'realisasiData',
                    'tahun_efektif',
                ],
            ])
            ->assertJsonPath('success', true);
    }

    /** Data realisasi pelanggan tampil sesuai yang diinsert */
    public function test_chart_pelanggan_returns_inserted_data(): void
    {
        // Insert realisasi JUMLAH bulan Januari
        RtFactPelanggan::create([
            'tahun'             => $this->tahun,
            'bulan'             => 1,
            'tipe'              => 'realisasi',
            'sub_golongan_kode' => 'JUMLAH',
            'golongan_kode'     => 'TOTAL',
            'nilai_pelanggan'   => 12345.0,
            'is_triwulan'       => false,
            'is_akumulasi'      => false,
            'sumber'            => 'manual',
        ]);

        $res = $this->getJson("/api/chart/pelanggan?tahun={$this->tahun}");

        $res->assertOk()
            ->assertJsonPath('data.tahun_efektif', $this->tahun);

        // Pastikan tarif JUMLAH tersedia
        $data  = $res->json('data');
        $tarif = $data['tarif'] ?? [];
        $this->assertContains('JUMLAH', $tarif);
    }

    /** Fallback ke tahun sebelumnya jika data tahun ini kosong */
    public function test_chart_pelanggan_fallback_ke_tahun_lalu(): void
    {
        $tahunLalu = $this->tahun - 1;

        RtFactPelanggan::create([
            'tahun'             => $tahunLalu,
            'bulan'             => 6,
            'tipe'              => 'realisasi',
            'sub_golongan_kode' => 'JUMLAH',
            'golongan_kode'     => 'TOTAL',
            'nilai_pelanggan'   => 9999.0,
            'is_triwulan'       => false,
            'is_akumulasi'      => false,
            'sumber'            => 'manual',
        ]);

        // Tidak ada data tahun ini → harus fallback ke tahun lalu
        $res = $this->getJson("/api/chart/pelanggan?tahun={$this->tahun}");

        $res->assertOk()
            ->assertJsonPath('data.tahun_efektif', $tahunLalu);
    }

    // ── /api/chart/tunggakan ─────────────────────────────────────────────────

    /** Endpoint tunggakan mengembalikan struktur yang benar */
    public function test_chart_tunggakan_returns_valid_structure(): void
    {
        $res = $this->getJson("/api/chart/tunggakan?tahun={$this->tahun}");

        $res->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data',
                    'tahun_efektif',
                    'is_fallback',
                ],
            ]);
    }

    /** Data tunggakan tampil sesuai yang diinsert */
    public function test_chart_tunggakan_returns_inserted_row(): void
    {
        RtFactTunggakan::create([
            'tahun'               => $this->tahun,
            'bulan'               => 3,
            'pal_berjalan'        => 100.0,
            'pal_tunggakan'       => 2.5,
            'ts_berjalan'         => 50.0,
            'ts_tunggakan'        => 1.0,
            'jumlah_tunggakan'    => 3.5,
            'pendapatan'          => 1_000_000.0,
            'ratio_tunggakan_pct' => 0.0035,
            'sumber'              => 'manual',
        ]);

        $res = $this->getJson("/api/chart/tunggakan?tahun={$this->tahun}");

        $res->assertOk();

        $rows = $res->json('data.data');
        $this->assertNotEmpty($rows);

        // Cari baris bulan Maret
        $maret = collect($rows)->first(fn ($r) => str_contains(strtolower($r['bulan'] ?? ''), 'mar'));
        $this->assertNotNull($maret, 'Baris bulan Maret tidak ditemukan');
        $this->assertEquals(1_000_000.0, $maret['pendapatan']);
    }

    /** Tunggakan fallback ke tahun lalu jika data tahun ini kosong */
    public function test_chart_tunggakan_fallback_ke_tahun_lalu(): void
    {
        RtFactTunggakan::create([
            'tahun'               => $this->tahun - 1,
            'bulan'               => 12,
            'pal_berjalan'        => 50.0,
            'pal_tunggakan'       => 1.0,
            'ts_berjalan'         => 20.0,
            'ts_tunggakan'        => 0.5,
            'jumlah_tunggakan'    => 1.5,
            'pendapatan'          => 500_000.0,
            'ratio_tunggakan_pct' => 0.003,
            'sumber'              => 'manual',
        ]);

        $res = $this->getJson("/api/chart/tunggakan?tahun={$this->tahun}");

        $res->assertOk()
            ->assertJsonPath('data.is_fallback', true)
            ->assertJsonPath('data.tahun_efektif', $this->tahun - 1);
    }

    // ── Endpoint-endpoint public lainnya ─────────────────────────────────────

    /** Semua chart endpoint harus return 200 OK meskipun data kosong */
    #[\PHPUnit\Framework\Attributes\DataProvider('chartEndpointProvider')]
    public function test_chart_endpoints_return_200_when_empty(string $endpoint): void
    {
        $res = $this->getJson("/api/chart/{$endpoint}?tahun={$this->tahun}");

        $res->assertOk()
            ->assertJsonPath('success', true);
    }

    public static function chartEndpointProvider(): array
    {
        return [
            'penjualan' => ['penjualan'],
            'losses'    => ['losses'],
            'bpp'       => ['bpp'],
            'hjr'       => ['hjr'],
            'jam-nyala' => ['jam-nyala'],
            'p2tl'      => ['p2tl'],
            'pendapatan'=> ['pendapatan'],
            'rpkwh'     => ['rpkwh'],
        ];
    }

    // ── /api/historis/pelanggan-stats ────────────────────────────────────────

    /** Endpoint stats pelanggan historis mengembalikan structure yang benar */
    public function test_historis_pelanggan_stats_returns_structure(): void
    {
        $res = $this->getJson("/api/historis/pelanggan-stats?tahun={$this->tahun}&bulan=6");

        $res->assertOk()
            ->assertJsonStructure([
                'tahun',
                'bulan',
                'nilai_tahun_ini',
                'nilai_tahun_lalu',
                'yoy_persen',
            ]);
    }

    /** yoy_persen null jika tidak ada data tahun lalu */
    public function test_historis_pelanggan_stats_yoy_null_without_data(): void
    {
        $res = $this->getJson("/api/historis/pelanggan-stats?tahun={$this->tahun}&bulan=6");

        $res->assertOk()
            ->assertJsonPath('yoy_persen', null);
    }
}
