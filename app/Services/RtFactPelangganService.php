<?php

namespace App\Services;

use App\Models\RtDimSubGolongan;
use App\Repositories\RtFactPelangganRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class RtFactPelangganService extends BaseService
{
    private const PREFIX_JUMLAH = 'Jumlah ';
    private const KODE_GRAND_TOTAL = 'JUMLAH';

    protected array $golonganList = ['S', 'R', 'B', 'I', 'P', 'M'];
    protected array $golonganJumlahKode = [
        'S' => 'JUMLAH-S', 'R' => 'JUMLAH-R', 'B' => 'JUMLAH-B',
        'I' => 'JUMLAH-I', 'P' => 'JUMLAH-P', 'M' => 'JUMLAH-M',
    ];

    /**
     * Pembagian 12 bulan (index 0-based, Jan=0 ... Des=11) ke 4 triwulan.
     * TW I: Jan-Mar, TW II: Apr-Jun, TW III: Jul-Sep, TW IV: Okt-Des.
     *
     * PENTING: setiap grup DIJUMLAH sendiri-sendiri (3 bulan di dalamnya),
     * tapi TIDAK dibawa/di-carry sebagai running total ke triwulan berikutnya.
     * Input Jan-Mar hanya memengaruhi TW I, sama sekali tidak menyentuh
     * TW II/III/IV.
     */
    protected array $twGroups = [
        1 => [0, 1, 2],
        2 => [3, 4, 5],
        3 => [6, 7, 8],
        4 => [9, 10, 11],
    ];

    public function __construct(RtFactPelangganRepository $repository)
    {
        parent::__construct($repository);
    }

    public function getChartData(int $tahun, ?int $tahunLalu = null): array
    {
        $availableYears = DB::table('rt_fact_pelanggan')
            ->distinct()->orderByDesc('tahun')->pluck('tahun')->values()->all();

        // Fallback ke tahun sebelumnya jika tidak ada data realisasi di tahun ini
        $cek = $this->repository->all([
            'tahun' => $tahun, 'tipe' => 'realisasi', 'is_triwulan' => 0, 'is_akumulasi' => 0,
        ]);

        if ($cek->isEmpty()) {
            $tahun = $tahun - 1;
        }

        $tahunLalu = $tahunLalu ?? $tahun - 1;

        // 1. Rencana & Realisasi per sub_golongan_kode (bulanan, is_akumulasi=0)
        $rencanaRows = $this->repository->all([
            'tahun' => $tahun, 'tipe' => 'target', 'is_triwulan' => 0, 'is_akumulasi' => 0,
        ]);
        $realisasiRows = $this->repository->all([
            'tahun' => $tahun, 'tipe' => 'realisasi', 'is_triwulan' => 0, 'is_akumulasi' => 0,
        ]);

        $rencanaData   = $this->pivotBySubGolongan($rencanaRows);
        $realisasiData = $this->pivotBySubGolongan($realisasiRows);

        $tarif = array_unique(array_merge(array_keys($rencanaData), array_keys($realisasiData)));
        sort($tarif);

        foreach ($tarif as $t) {
            $rencanaData[$t]   = $rencanaData[$t]   ?? array_fill(0, 12, 0);
            $realisasiData[$t] = $realisasiData[$t] ?? array_fill(0, 12, 0);
        }

        // 2. Target golongan akumulasi tahun ini & realisasi golongan akumulasi tahun lalu
        $targetGolRows = $this->repository->all([
            'tahun' => $tahun, 'tipe' => 'target', 'is_triwulan' => 0, 'is_akumulasi' => 1,
        ]);
        $realisasiGolRowsLalu = $this->repository->all([
            'tahun' => $tahunLalu, 'tipe' => 'realisasi', 'is_triwulan' => 0, 'is_akumulasi' => 1,
        ]);

        $targetGolAkum    = $this->pivotByGolonganJumlah($targetGolRows);
        $realisasiGolAkum = $this->pivotByGolonganJumlah($realisasiGolRowsLalu);

        foreach ($this->golonganList as $g) {
            $key = self::PREFIX_JUMLAH . $this->golonganNama($g) . ' (' . $g . ')';
            $targetGolAkum[$key]    = $targetGolAkum[$key]    ?? array_fill(0, 12, 0);
            $realisasiGolAkum[$key] = $realisasiGolAkum[$key] ?? array_fill(0, 12, 0);
        }

        $golongan = array_map(
            fn ($g) => self::PREFIX_JUMLAH . $this->golonganNama($g) . ' (' . $g . ')',
            $this->golonganList
        );

        // 3. RKAP — per golongan TW1-4 target/realisasi, dihitung dari data bulanan
        $rkapData = $this->buildRkapData($tahun);

        return [
            'tarif'            => $tarif,
            'golongan'         => $golongan,
            'rencanaData'      => $rencanaData,
            'realisasiData'    => $realisasiData,
            'targetGolAkum'    => $targetGolAkum,
            'realisasiGolAkum' => $realisasiGolAkum,
            'rkapData'         => $rkapData,
            'tahun_efektif'    => $tahun,
            'tahun_lalu'       => $tahunLalu,
            'availableYears'   => $availableYears,
        ];
    }

    /**
     * Backend adalah source of truth untuk golongan_kode.
     * Nilai golongan_kode yang dikirim frontend akan DIABAIKAN dan
     * dihitung ulang dari sub_golongan_kode via rt_dim_sub_golongan.
     * Mencegah golongan_kode salah tersimpan (mis. hasil bug frontend).
     */
    protected function resolveGolonganKode(?string $subGolonganKode): ?string
    {
        if (!$subGolonganKode) {
            return null;
        }

        static $map = null;
        if ($map === null) {
            $map = RtDimSubGolongan::pluck('golongan_kode', 'kode')->all();
        }

        return $map[$subGolonganKode] ?? null;
    }

    public function create(array $data): Model
    {
        if (!empty($data['sub_golongan_kode'])) {
            $resolved = $this->resolveGolonganKode($data['sub_golongan_kode']);
            if ($resolved !== null) {
                $data['golongan_kode'] = $resolved;
            }
        }

        $model = parent::create($data);
        $this->syncAggregates($data);

        return $model;
    }

    public function update(int|string $id, array $data): Model
    {
        if (!empty($data['sub_golongan_kode'])) {
            $resolved = $this->resolveGolonganKode($data['sub_golongan_kode']);
            if ($resolved !== null) {
                $data['golongan_kode'] = $resolved;
            }
        }

        $model = parent::update($id, $data);
        $this->syncAggregates($data);

        return $model;
    }

    /**
     * Entry point sinkronisasi otomatis. Dipanggil setelah create/update
     * pada rt_fact_pelanggan. Menentukan level baris yang baru disimpan,
     * lalu meneruskan ke sinkronisasi golongan (JUMLAH-S dst), grand total
     * (JUMLAH), triwulan, dan akumulasi berjalan — semua lewat query
     * builder langsung (bukan create()/update() service ini lagi) supaya
     * tidak infinite loop.
     */
    protected function syncAggregates(array $data): void
    {
        $subKode = $data['sub_golongan_kode'] ?? null;
        if (!$subKode || $subKode === self::KODE_GRAND_TOTAL) {
            return;
        }

        $isJumlahGolongan = str_starts_with($subKode, 'JUMLAH-');

        if (!$isJumlahGolongan) {
            // Sub-golongan biasa (mis. S-2/1300 VA) → hitung ulang JUMLAH-{golongan}-nya
            // (di dalamnya juga memicu hitung ulang akumulasi berjalan)
            $this->syncJumlahGolongan($data);
        } else {
            // JUMLAH-{golongan} diedit langsung → hitung ulang akumulasi berjalan miliknya sendiri
            [$tahun, , $tipe, $isTriwulan, $isAkumulasi] = $this->extractKeys($data);
            if ($tahun !== null && $tipe !== null && $isTriwulan == 0 && $isAkumulasi == 0) {
                $golKode = $this->resolveGolonganKode($subKode);
                $this->syncAkumulasiGolongan($tahun, $tipe, $subKode, $golKode);
            }
        }

        // Baik sub-golongan biasa maupun JUMLAH-{golongan} yang diedit langsung
        // sama-sama harus memicu hitung ulang grand total JUMLAH.
        $this->syncGrandTotal($data);

        // Auto-isi triwulan — hanya dipicu dari baris BULANAN asli
        // (is_triwulan=0, is_akumulasi=0). Guard di dalam method.
        $this->syncTriwulan($data);
    }

    /**
     * Hitung ulang total satu golongan (mis. JUMLAH-S) dari SUM seluruh
     * sub-golongan aslinya (bukan baris JUMLAH-*), untuk kombinasi
     * tahun/bulan/tipe/is_triwulan/is_akumulasi yang sama dengan baris
     * yang baru disimpan, lalu upsert ke baris JUMLAH-{golongan}.
     *
     * Setelah itu, kalau ini baris bulanan asli (is_triwulan=0, is_akumulasi=0),
     * hitung ulang juga seri akumulasi berjalan (Jan→Des) untuk golongan ini —
     * dipakai oleh chart/tabel Distribusi Golongan Pelanggan.
     */
    protected function syncJumlahGolongan(array $data): void
    {
        $subKode = $data['sub_golongan_kode'] ?? null;
        $golKode = $this->resolveGolonganKode($subKode);
        if (!$golKode || !isset($this->golonganJumlahKode[$golKode])) {
            return;
        }

        [$tahun, $bulan, $tipe, $isTriwulan, $isAkumulasi] = $this->extractKeys($data);
        if ($tahun === null || $bulan === null || $tipe === null) {
            return;
        }

        $total = DB::table('rt_fact_pelanggan')
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->where('tipe', $tipe)
            ->where('golongan_kode', $golKode)
            ->where('is_triwulan', $isTriwulan)
            ->where('is_akumulasi', $isAkumulasi)
            ->where('sub_golongan_kode', 'not like', 'JUMLAH%')
            ->sum('nilai_pelanggan');

        $jumlahKode = $this->golonganJumlahKode[$golKode];

        DB::table('rt_fact_pelanggan')->updateOrInsert(
            [
                'tahun'             => $tahun,
                'bulan'             => $bulan,
                'tipe'              => $tipe,
                'sub_golongan_kode' => $jumlahKode,
                'is_triwulan'       => $isTriwulan,
                'is_akumulasi'      => $isAkumulasi,
            ],
            [
                'golongan_kode'   => $golKode,
                'nilai_pelanggan' => $total,
                'sumber'          => 'manual',
                'updated_at'      => now(),
                'created_at'      => now(),
            ]
        );

        // Hanya recompute akumulasi berjalan untuk baris bulanan asli —
        // bukan saat method ini dipanggil ulang dari konteks triwulan.
        if ($isTriwulan == 0 && $isAkumulasi == 0) {
            $this->syncAkumulasiGolongan($tahun, $tipe, $jumlahKode, $golKode);
        }
    }

    /**
     * Hitung ulang grand total (sub_golongan_kode = 'JUMLAH') dari SUM
     * seluruh baris JUMLAH-{golongan} (S/R/B/I/P/M), untuk kombinasi
     * tahun/bulan/tipe/is_triwulan/is_akumulasi yang sama.
     *
     * golongan_kode diisi self::KODE_GRAND_TOTAL ('JUMLAH') — bukan null —
     * karena kolom golongan_kode di tabel bersifat NOT NULL.
     */
    protected function syncGrandTotal(array $data): void
    {
        [$tahun, $bulan, $tipe, $isTriwulan, $isAkumulasi] = $this->extractKeys($data);
        if ($tahun === null || $bulan === null || $tipe === null) {
            return;
        }

        $jumlahKodeList = array_values($this->golonganJumlahKode);

        $total = DB::table('rt_fact_pelanggan')
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->where('tipe', $tipe)
            ->where('is_triwulan', $isTriwulan)
            ->where('is_akumulasi', $isAkumulasi)
            ->whereIn('sub_golongan_kode', $jumlahKodeList)
            ->sum('nilai_pelanggan');

        DB::table('rt_fact_pelanggan')->updateOrInsert(
            [
                'tahun'             => $tahun,
                'bulan'             => $bulan,
                'tipe'              => $tipe,
                'sub_golongan_kode' => self::KODE_GRAND_TOTAL,
                'is_triwulan'       => $isTriwulan,
                'is_akumulasi'      => $isAkumulasi,
            ],
            [
                'golongan_kode'   => self::KODE_GRAND_TOTAL,
                'nilai_pelanggan' => $total,
                'sumber'          => 'manual',
                'updated_at'      => now(),
                'created_at'      => now(),
            ]
        );
    }

    /**
     * Auto-isi baris triwulan dari data bulanan.
     *
     * Nilai triwulan = SUM 3 bulan penyusun kuartal tersebut SENDIRI-SENDIRI
     * (TW I = Jan+Feb+Mar, TW II = Apr+Mei+Jun, dst) — TIDAK kumulatif/
     * running-total dari awal tahun. Baris triwulan disimpan di tabel yang
     * sama dengan is_triwulan=1, kolom `bulan` diisi 1-4 (mewakili TW1-TW4).
     *
     * Hanya dipicu dari baris bulanan asli (is_triwulan=0, is_akumulasi=0)
     * — baik sub-golongan biasa maupun JUMLAH-{golongan} — supaya tidak
     * dobel proses saat baris agregat lain ikut ter-upsert.
     */
    protected function syncTriwulan(array $data): void
    {
        $subKode     = $data['sub_golongan_kode'] ?? null;
        $bulan       = $data['bulan'] ?? null;
        $isTriwulan  = $data['is_triwulan']  ?? 0;
        $isAkumulasi = $data['is_akumulasi'] ?? 0;

        if (!$subKode || !$bulan || $isTriwulan != 0 || $isAkumulasi != 0) {
            return;
        }

        [$tahun, , $tipe] = $this->extractKeys($data);
        if ($tahun === null || $tipe === null) {
            return;
        }

        $tw           = (int) ceil($bulan / 3);
        $bulanDalamTw = [$tw * 3 - 2, $tw * 3 - 1, $tw * 3];

        $total = DB::table('rt_fact_pelanggan')
            ->where('tahun', $tahun)
            ->where('tipe', $tipe)
            ->where('sub_golongan_kode', $subKode)
            ->where('is_triwulan', 0)
            ->where('is_akumulasi', 0)
            ->whereIn('bulan', $bulanDalamTw)
            ->sum('nilai_pelanggan');

        $golKode = $this->resolveGolonganKode($subKode);

        DB::table('rt_fact_pelanggan')->updateOrInsert(
            [
                'tahun'             => $tahun,
                'bulan'             => $tw,
                'tipe'              => $tipe,
                'sub_golongan_kode' => $subKode,
                'is_triwulan'       => 1,
                'is_akumulasi'      => 0,
            ],
            [
                'golongan_kode'   => $golKode,
                'nilai_pelanggan' => $total,
                'sumber'          => 'manual',
                'updated_at'      => now(),
                'created_at'      => now(),
            ]
        );

        // Propagasi ke JUMLAH-{golongan} dan JUMLAH grand total, level triwulan
        $twData = [
            'tahun' => $tahun, 'bulan' => $tw, 'tipe' => $tipe,
            'sub_golongan_kode' => $subKode, 'is_triwulan' => 1, 'is_akumulasi' => 0,
        ];

        if (!str_starts_with($subKode, 'JUMLAH')) {
            $this->syncJumlahGolongan($twData);
        }

        $this->syncGrandTotal($twData);
    }

    /**
     * Hitung ulang seri akumulasi berjalan (running cumulative Jan→Des)
     * untuk satu golongan (mis. JUMLAH-S), berdasarkan nilai bulanan
     * (is_triwulan=0, is_akumulasi=0) yang sudah ada. Hasilnya disimpan
     * sebagai 12 baris terpisah dengan is_akumulasi=1 — inilah yang dibaca
     * chart pie & tabel "Distribusi Golongan Pelanggan" (targetGolAkum /
     * realisasiGolAkum).
     *
     * Contoh: target Jan=10, Apr=22 (bulan lain 0) →
     * akumulasi: Jan=10, Feb=10, Mar=10, Apr=32, Mei=32, ..., Des=32.
     */
    protected function syncAkumulasiGolongan(int $tahun, string $tipe, string $jumlahKode, ?string $golKode): void
    {
        $bulananValues = DB::table('rt_fact_pelanggan')
            ->where('tahun', $tahun)
            ->where('tipe', $tipe)
            ->where('sub_golongan_kode', $jumlahKode)
            ->where('is_triwulan', 0)
            ->where('is_akumulasi', 0)
            ->pluck('nilai_pelanggan', 'bulan');

        $running = 0;
        $now     = now();

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $running += (float) ($bulananValues[$bulan] ?? 0);

            DB::table('rt_fact_pelanggan')->updateOrInsert(
                [
                    'tahun'             => $tahun,
                    'bulan'             => $bulan,
                    'tipe'              => $tipe,
                    'sub_golongan_kode' => $jumlahKode,
                    'is_triwulan'       => 0,
                    'is_akumulasi'      => 1,
                ],
                [
                    'golongan_kode'   => $golKode,
                    'nilai_pelanggan' => $running,
                    'sumber'          => 'manual',
                    'updated_at'      => $now,
                    'created_at'      => $now,
                ]
            );
        }
    }

    protected function extractKeys(array $data): array
    {
        return [
            $data['tahun'] ?? null,
            $data['bulan'] ?? null,
            $data['tipe'] ?? null,
            $data['is_triwulan']  ?? 0,
            $data['is_akumulasi'] ?? 0,
        ];
    }

    /**
     * Generate 12 baris/bulan (nilai=0) untuk satu tahun+tipe, berdasarkan
     * SEMUA baris rt_dim_sub_golongan — mencakup tarif asli DAN JUMLAH-S/R/B/I/P/M
     * DAN baris JUMLAH/TOTAL grand-total. golongan_kode diambil langsung dari
     * dim table, tidak ditebak/hardcode di frontend.
     */
    public function buatTahun(int $tahun, string $tipe): array
    {
        return DB::transaction(function () use ($tahun, $tipe) {
            $now  = now();
            $rows = [];

            $dims = RtDimSubGolongan::select('kode', 'golongan_kode')->get();

            foreach ($dims as $dim) {
                $isAkumulasi = str_starts_with($dim->kode, 'JUMLAH') ? 1 : 0;

                for ($bulan = 1; $bulan <= 12; $bulan++) {
                    $rows[] = [
                        'tahun'             => $tahun,
                        'bulan'             => $bulan,
                        'tipe'              => $tipe,
                        'golongan_kode'     => $dim->golongan_kode,
                        'sub_golongan_kode' => $dim->kode,
                        'nilai_pelanggan'   => 0,
                        'is_triwulan'       => 0,
                        'is_akumulasi'      => $isAkumulasi,
                        'sumber'            => 'manual',
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ];
                }
            }

            DB::table('rt_fact_pelanggan')->insertOrIgnore($rows);

            return ['tahun' => $tahun, 'tipe' => $tipe, 'rows_created' => count($rows)];
        });
    }

    protected function pivotBySubGolongan($rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $key = $row->sub_golongan_kode;
            if (!isset($result[$key])) {
                $result[$key] = array_fill(0, 12, 0);
            }
            $result[$key][$row->bulan - 1] = (float) $row->nilai_pelanggan;
        }
        return $result;
    }

    protected function pivotByGolonganJumlah($rows): array
    {
        $result = [];
        $map = array_flip($this->golonganJumlahKode);

        foreach ($rows as $row) {
            $kode = $row->sub_golongan_kode;
            if (!isset($map[$kode])) {
                continue;
            }

            $g   = $map[$kode];
            $key = self::PREFIX_JUMLAH . $this->golonganNama($g) . ' (' . $g . ')';

            if (!isset($result[$key])) {
                $result[$key] = array_fill(0, 12, 0);
            }
            $result[$key][$row->bulan - 1] = (float) $row->nilai_pelanggan;
        }
        return $result;
    }

    protected function golonganNama(string $kode): string
    {
        return match ($kode) {
            'S' => 'Sosial',
            'R' => 'Rumah Tangga',
            'B' => 'Bisnis',
            'I' => 'Industri',
            'P' => 'Publik',
            'M' => 'Multiguna',
            default => $kode,
        };
    }

    /**
     * Bangun data RKAP (TW1-4 + total + pencapaian) per golongan.
     *
     * Dihitung dari data BULANAN (is_akumulasi=0) — sumber yang pasti ada
     * karena diisi lewat tombol "Buat Data"/edit manual.
     *
     * PENTING: setiap TW = SUM 3 bulan penyusunnya SENDIRI-SENDIRI
     * (TW I = Jan+Feb+Mar, TW II = Apr+Mei+Jun, TW III = Jul+Agu+Sep,
     * TW IV = Okt+Nov+Des). TIDAK kumulatif/running-total — input di
     * Jan-Mar HANYA memengaruhi TW I, tidak menyentuh TW II/III/IV.
     */
    protected function buildRkapData(int $tahun): array
    {
        $rkapData = [];

        $golonganMap = [
            'S' => 'Sosial (S)', 'R' => 'Rumah Tangga (R)', 'B' => 'Bisnis (B)',
            'I' => 'Industri (I)', 'P' => 'Publik (P)', 'M' => 'Multiguna (M)',
        ];

        $targetRows = $this->repository->all([
            'tahun' => $tahun, 'tipe' => 'target', 'is_triwulan' => 0, 'is_akumulasi' => 0,
        ]);
        $realRows = $this->repository->all([
            'tahun' => $tahun, 'tipe' => 'realisasi', 'is_triwulan' => 0, 'is_akumulasi' => 0,
        ]);

        $targetBulanan = $this->pivotBySubGolongan($targetRows);
        $realBulanan   = $this->pivotBySubGolongan($realRows);

        // Total per TW across semua golongan — untuk baris JUMLAH (grand total)
        $grandTarget = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
        $grandReal   = [1 => 0, 2 => 0, 3 => 0, 4 => 0];

        foreach ($golonganMap as $kode => $label) {
            $jumlahKode = $this->golonganJumlahKode[$kode];

            $targetBul = $targetBulanan[$jumlahKode] ?? array_fill(0, 12, 0);
            $realBul   = $realBulanan[$jumlahKode]   ?? array_fill(0, 12, 0);

            $target = [];
            $real   = [];
            foreach ($this->twGroups as $tw => $bulanIdx) {
                $target[$tw] = array_sum(array_map(fn ($i) => $targetBul[$i] ?? 0, $bulanIdx));
                $real[$tw]   = array_sum(array_map(fn ($i) => $realBul[$i]   ?? 0, $bulanIdx));

                $grandTarget[$tw] += $target[$tw];
                $grandReal[$tw]   += $real[$tw];
            }

            $targetTotal = $target[1] + $target[2] + $target[3] + $target[4];
            $realTotal   = $real[1]   + $real[2]   + $real[3]   + $real[4];

            $rkapData[] = [
                'golongan'    => $label,
                'targetTw1'   => $target[1],
                'targetTw2'   => $target[2],
                'targetTw3'   => $target[3],
                'targetTw4'   => $target[4],
                'targetTotal' => $targetTotal,
                'realTw1'     => $real[1],
                'realTw2'     => $real[2],
                'realTw3'     => $real[3],
                'realTw4'     => $real[4],
                'realTotal'   => $realTotal,
                'pencapaian'  => $targetTotal > 0 ? $realTotal / $targetTotal : 0,
            ];
        }

        // Baris JUMLAH (grand total) — SUM per TW dari semua golongan di atas
        $targetTotalAll = $grandTarget[1] + $grandTarget[2] + $grandTarget[3] + $grandTarget[4];
        $realTotalAll   = $grandReal[1]   + $grandReal[2]   + $grandReal[3]   + $grandReal[4];

        $rkapData[] = [
            'golongan'    => 'JUMLAH',
            'targetTw1'   => $grandTarget[1],
            'targetTw2'   => $grandTarget[2],
            'targetTw3'   => $grandTarget[3],
            'targetTw4'   => $grandTarget[4],
            'targetTotal' => $targetTotalAll,
            'realTw1'     => $grandReal[1],
            'realTw2'     => $grandReal[2],
            'realTw3'     => $grandReal[3],
            'realTw4'     => $grandReal[4],
            'realTotal'   => $realTotalAll,
            'pencapaian'  => $targetTotalAll > 0 ? $realTotalAll / $targetTotalAll : 0,
        ];

        return $rkapData;
    }
}