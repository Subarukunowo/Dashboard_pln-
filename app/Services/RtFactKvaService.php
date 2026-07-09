<?php

namespace App\Services;

use App\Models\RtDimSubGolongan;
use App\Repositories\RtFactKvaRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;

class RtFactKvaService extends BaseService
{
    protected array $golonganList = ['S', 'R', 'B', 'I', 'P', 'M'];

    protected array $golonganJumlahKode = [
        'S' => 'JUMLAH-S', 'R' => 'JUMLAH-R', 'B' => 'JUMLAH-B',
        'I' => 'JUMLAH-I', 'P' => 'JUMLAH-P', 'M' => 'JUMLAH-M',
    ];

    /** Pemetaan bulan -> triwulan (Q1: Jan-Mar, Q2: Apr-Jun, Q3: Jul-Sep, Q4: Okt-Des) */
    protected array $triwulanMap = [
        1 => [1, 2, 3],
        2 => [4, 5, 6],
        3 => [7, 8, 9],
        4 => [10, 11, 12],
    ];

    public function __construct(RtFactKvaRepository $repository)
    {
        parent::__construct($repository);
    }

    // ============================================================
    // QUERY HELPERS (menggantikan applyFilters/updateOrCreate/deleteByFilter
    // yang tidak tersedia di BaseRepository bawaan)
    // ============================================================

    /**
     * Bangun Eloquent Builder dari model repository dengan filter key => value.
     * Value array/Collection otomatis pakai whereIn().
     */
    protected function filteredQuery(array $filters = []): Builder
    {
        $query = $this->repository->getModel()->newQuery();

        foreach ($filters as $column => $value) {
            if ($value === null) {
                continue;
            }

            if ($value instanceof SupportCollection) {
                $value = $value->all();
            }

            if (is_array($value)) {
                $query->whereIn($column, $value);
            } else {
                $query->where($column, $value);
            }
        }

        return $query;
    }

    /**
     * Pengganti $this->repository->all($filters) — dipakai di seluruh service ini
     * karena BaseRepository::all() tidak benar-benar memfilter (applyFilters stub kosong).
     */
    protected function allFiltered(array $filters = []): Collection
    {
        return $this->filteredQuery($filters)->get();
    }

    /**
     * Pengganti $this->repository->updateOrCreate($attributes, $values).
     */
    protected function updateOrCreateRow(array $attributes, array $values = []): Model
    {
        return $this->filteredQuery($attributes)->first()
            ? tap($this->filteredQuery($attributes)->first())->update($values)
            : $this->repository->create(array_merge($attributes, $values));
    }

    /**
     * Pengganti $this->repository->deleteByFilter($filters).
     */
    protected function deleteFiltered(array $filters): int
    {
        return $this->filteredQuery($filters)->delete();
    }

    // ============================================================
    // CHART DATA
    // ============================================================

    public function getChartData(int $tahun, ?int $tahunLalu = null): array
    {
        $cek = $this->allFiltered([
            'tahun' => $tahun, 'tipe' => 'realisasi', 'is_triwulan' => 0, 'is_akumulasi' => 0,
        ]);
        if ($cek->isEmpty()) {
            $tahun = $tahun - 1;
        }
        $tahunLalu = $tahunLalu ?? $tahun - 1;

        $rencanaRows = $this->allFiltered([
            'tahun' => $tahun, 'tipe' => 'target', 'is_triwulan' => 0, 'is_akumulasi' => 0,
        ]);
        $realisasiRows = $this->allFiltered([
            'tahun' => $tahun, 'tipe' => 'realisasi', 'is_triwulan' => 0, 'is_akumulasi' => 0,
        ]);

        $rencanaData   = $this->pivotKva($rencanaRows);
        $realisasiData = $this->pivotKva($realisasiRows);

        $tarif = array_unique(array_merge(array_keys($rencanaData), array_keys($realisasiData)));
        sort($tarif);
        foreach ($tarif as $t) {
            $rencanaData[$t]   = $rencanaData[$t]   ?? array_fill(0, 12, 0);
            $realisasiData[$t] = $realisasiData[$t] ?? array_fill(0, 12, 0);
        }

        $targetGolRows = $this->allFiltered([
            'tahun' => $tahun, 'tipe' => 'target', 'is_triwulan' => 0, 'is_akumulasi' => 1,
        ]);
        $realisasiGolRowsLalu = $this->allFiltered([
            'tahun' => $tahunLalu, 'tipe' => 'realisasi', 'is_triwulan' => 0, 'is_akumulasi' => 1,
        ]);

        $targetGolAkum    = $this->pivotGolonganKva($targetGolRows);
        $realisasiGolAkum = $this->pivotGolonganKva($realisasiGolRowsLalu);

        $golongan = array_map(
            fn ($g) => 'Jumlah ' . $this->golonganNama($g) . ' (' . $g . ')',
            $this->golonganList
        );
        foreach ($golongan as $key) {
            $targetGolAkum[$key]    = $targetGolAkum[$key]    ?? array_fill(0, 12, 0);
            $realisasiGolAkum[$key] = $realisasiGolAkum[$key] ?? array_fill(0, 12, 0);
        }

        return [
            'tarif'             => $tarif,
            'golongan'          => $golongan,
            'golonganDropdown'  => $this->golonganDropdown(),
            'rencanaData'       => $rencanaData,
            'realisasiData'     => $realisasiData,
            'targetGolAkum'     => $targetGolAkum,
            'realisasiGolAkum'  => $realisasiGolAkum,
            'rkapData'          => $this->buildRkapKva($tahun),
            'perbandinganData'  => $this->buildPerbandingan($tahun, $tahunLalu),
            'tahun_efektif'     => $tahun,
        ];
    }

    /**
     * Dropdown kode sub golongan dari rt_dim_sub_golongan (read-only, tabel sudah ada).
     */
    protected function golonganDropdown(): array
    {
        return RtDimSubGolongan::orderBy('golongan_kode')->orderBy('urutan')->get()
            ->groupBy('golongan_kode')
            ->map(fn ($items) => $items->map(fn ($i) => [
                'kode'   => $i->kode,
                'nama'   => $i->nama,
                'jumlah' => str_starts_with($i->kode, 'JUMLAH'),
            ])->values())
            ->toArray();
    }

    protected function pivotKva($rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $key = $row->sub_golongan_kode;
            if (!isset($result[$key])) {
                $result[$key] = array_fill(0, 12, 0);
            }
            $result[$key][$row->bulan - 1] = (float) ($row->nilai_kva ?? 0);
        }
        return $result;
    }

    protected function pivotGolonganKva($rows): array
    {
        $result = [];
        $map = array_flip($this->golonganJumlahKode);

        foreach ($rows as $row) {
            $kode = $row->sub_golongan_kode;
            if (!isset($map[$kode])) {
                continue;
            }
            $g   = $map[$kode];
            $key = 'Jumlah ' . $this->golonganNama($g) . ' (' . $g . ')';

            if (!isset($result[$key])) {
                $result[$key] = array_fill(0, 12, 0);
            }
            $result[$key][$row->bulan - 1] = (float) ($row->nilai_kva ?? 0);
        }
        return $result;
    }

    protected function golonganNama(string $kode): string
    {
        return match ($kode) {
            'S' => 'Sosial', 'R' => 'Rumah Tangga', 'B' => 'Bisnis',
            'I' => 'Industri', 'P' => 'Publik', 'M' => 'Multiguna',
            default => $kode,
        };
    }

    // ============================================================
    // TRIWULAN & PERSENTASE (util umum)
    // ============================================================

    protected function getTriwulan(int $bulan): int
    {
        foreach ($this->triwulanMap as $tw => $bulans) {
            if (in_array($bulan, $bulans, true)) {
                return $tw;
            }
        }
        throw new \InvalidArgumentException("Bulan tidak valid: {$bulan}");
    }

    protected function hitungPersentase(float $target, float $real): float
    {
        return $target > 0 ? round($real / $target, 4) : 0.0;
    }

    /**
     * Hitung ulang nilai per-triwulan (is_triwulan=1) dari data bulanan (is_triwulan=0)
     * untuk satu sub_golongan_kode + tipe + tahun tertentu.
     */
    protected function hitungTriwulan(int $tahun, string $tipe, string $subGolonganKode): void
    {
        $bulanan = $this->allFiltered([
            'tahun' => $tahun, 'tipe' => $tipe, 'sub_golongan_kode' => $subGolonganKode,
            'is_triwulan' => 0, 'is_akumulasi' => 0,
        ])->keyBy('bulan');

        $golonganKode = $this->resolveGolonganKode($subGolonganKode);

        foreach ($this->triwulanMap as $tw => $bulans) {
            $total = collect($bulans)->sum(
                fn ($b) => (float) ($bulanan->get($b)->nilai_kva ?? 0)
            );

            $this->updateOrCreateRow(
                [
                    'tahun' => $tahun, 'tipe' => $tipe, 'sub_golongan_kode' => $subGolonganKode,
                    'bulan' => $tw, 'is_triwulan' => 1, 'is_akumulasi' => 0,
                ],
                [
                    'nilai_kva' => $total, 'golongan_kode' => $golonganKode,
                    'sumber' => 'manual',
                ]
            );
        }
    }

    /**
     * Hitung ulang running total (akumulasi Jan..Des) untuk satu sub_golongan_kode.
     */
    protected function hitungAkumulasi(int $tahun, string $tipe, string $subGolonganKode): void
    {
        $bulanan = $this->allFiltered([
            'tahun' => $tahun, 'tipe' => $tipe, 'sub_golongan_kode' => $subGolonganKode,
            'is_triwulan' => 0, 'is_akumulasi' => 0,
        ])->keyBy('bulan');

        $golonganKode = $this->resolveGolonganKode($subGolonganKode);
        $running = 0.0;

        for ($b = 1; $b <= 12; $b++) {
            $running += (float) ($bulanan->get($b)->nilai_kva ?? 0);

            $this->updateOrCreateRow(
                [
                    'tahun' => $tahun, 'tipe' => $tipe, 'sub_golongan_kode' => $subGolonganKode,
                    'bulan' => $b, 'is_triwulan' => 0, 'is_akumulasi' => 1,
                ],
                [
                    'nilai_kva' => $running, 'golongan_kode' => $golonganKode,
                    'sumber' => 'manual',
                ]
            );
        }
    }

    protected function resolveGolonganKode(string $subGolonganKode): string
    {
        if ($subGolonganKode === 'JUMLAH') {
            return 'TOTAL';
        }
        if (str_starts_with($subGolonganKode, 'JUMLAH-')) {
            return substr($subGolonganKode, 7);
        }
        $dim = RtDimSubGolongan::find($subGolonganKode);
        return $dim->golongan_kode ?? substr($subGolonganKode, 0, 1);
    }

    // ============================================================
    // UPDATE SATU TITIK DATA + AUTO REHITUNG (poin 3 & 5)
    // ============================================================

    /**
     * Entry point saat user edit nilai_kva di blade (level sub golongan, bulanan, non-akumulasi).
     * Tidak mengubah nilai sub golongan lain — hanya menulis ulang baris JUMLAH-{golongan},
     * JUMLAH total, triwulan, dan akumulasi.
     */
    public function updateNilaiDanRehitung(
        int $tahun,
        int $bulan,
        string $subGolonganKode,
        string $tipe,
        float $nilaiKva
    ): array {
        $isJumlahBaris = $subGolonganKode === 'JUMLAH' || str_starts_with($subGolonganKode, 'JUMLAH-');
        $golonganKode  = $this->resolveGolonganKode($subGolonganKode);

        // 1. Simpan nilai bulanan mentah untuk sub golongan yang diedit
        $this->updateOrCreateRow(
            [
                'tahun' => $tahun, 'tipe' => $tipe, 'sub_golongan_kode' => $subGolonganKode,
                'bulan' => $bulan, 'is_triwulan' => 0, 'is_akumulasi' => 0,
            ],
            [
                'nilai_kva' => $nilaiKva, 'golongan_kode' => $golonganKode,
                'sumber' => 'manual',
            ]
        );

        $terpengaruh = [$subGolonganKode];

        // 2. Jika yang diedit BUKAN baris JUMLAH, rehitung agregat di atasnya
        if (!$isJumlahBaris) {
            $this->rehitungJumlahGolongan($tahun, $tipe, $golonganKode, $bulan);
            $this->rehitungJumlahTotal($tahun, $tipe, $bulan);
            $terpengaruh[] = 'JUMLAH-' . $golonganKode;
            $terpengaruh[] = 'JUMLAH';
        } elseif ($subGolonganKode !== 'JUMLAH') {
            // Yang diedit JUMLAH-{golongan} langsung -> tetap rehitung JUMLAH total
            $this->rehitungJumlahTotal($tahun, $tipe, $bulan);
            $terpengaruh[] = 'JUMLAH';
        }

        // 3. Rehitung triwulan & akumulasi untuk semua kode yang terpengaruh
        foreach (array_unique($terpengaruh) as $kode) {
            $this->hitungTriwulan($tahun, $tipe, $kode);
            $this->hitungAkumulasi($tahun, $tipe, $kode);
        }

        return [
            'tahun'             => $tahun,
            'bulan'             => $bulan,
            'tipe'              => $tipe,
            'sub_golongan_kode' => $subGolonganKode,
            'nilai_kva'         => $nilaiKva,
            'terpengaruh'       => array_values(array_unique($terpengaruh)),
        ];
    }

    /**
     * JUMLAH-{golongan} bulanan = sum semua sub golongan anggota golongan tsb (bukan JUMLAH-*).
     */
    protected function rehitungJumlahGolongan(int $tahun, string $tipe, string $golonganKode, int $bulan): void
    {
        $kodeAnggota = RtDimSubGolongan::where('golongan_kode', $golonganKode)
            ->where('kode', 'not like', 'JUMLAH%')
            ->pluck('kode');

        $total = $this->allFiltered([
            'tahun' => $tahun, 'tipe' => $tipe, 'bulan' => $bulan,
            'is_triwulan' => 0, 'is_akumulasi' => 0,
            'sub_golongan_kode' => $kodeAnggota,
        ])->sum(fn ($r) => (float) ($r->nilai_kva ?? 0));

        $this->updateOrCreateRow(
            [
                'tahun' => $tahun, 'tipe' => $tipe, 'bulan' => $bulan,
                'sub_golongan_kode' => 'JUMLAH-' . $golonganKode,
                'is_triwulan' => 0, 'is_akumulasi' => 0,
            ],
            [
                'nilai_kva' => $total, 'golongan_kode' => $golonganKode,
                'sumber' => 'manual',
            ]
        );
    }

    /**
     * JUMLAH total bulanan = sum seluruh JUMLAH-{golongan}.
     */
    protected function rehitungJumlahTotal(int $tahun, string $tipe, int $bulan): void
    {
        $total = 0.0;
        foreach ($this->golonganList as $g) {
            $row = $this->allFiltered([
                'tahun' => $tahun, 'tipe' => $tipe, 'bulan' => $bulan,
                'sub_golongan_kode' => 'JUMLAH-' . $g,
                'is_triwulan' => 0, 'is_akumulasi' => 0,
            ])->first();
            $total += (float) ($row->nilai_kva ?? 0);
        }

        $this->updateOrCreateRow(
            [
                'tahun' => $tahun, 'tipe' => $tipe, 'bulan' => $bulan,
                'sub_golongan_kode' => 'JUMLAH', 'is_triwulan' => 0, 'is_akumulasi' => 0,
            ],
            [
                'nilai_kva' => $total, 'golongan_kode' => 'TOTAL',
                'sumber' => 'manual',
            ]
        );
    }

    // ============================================================
    // RKAP (poin 2 & 4)
    // ============================================================

    protected function buildRkapKva(int $tahun): array
    {
        $rkapData = [];
        $golonganMap = [
            'S' => 'Sosial (S)', 'R' => 'Rumah Tangga (R)', 'B' => 'Bisnis (B)',
            'I' => 'Industri (I)', 'P' => 'Publik (P)', 'M' => 'Multiguna (M)',
        ];

        $targetTw = $this->allFiltered([
            'tahun' => $tahun, 'tipe' => 'target', 'is_triwulan' => 1, 'is_akumulasi' => 0,
        ])->keyBy(fn ($r) => $r->sub_golongan_kode . '-' . $r->bulan);

        $realTw = $this->allFiltered([
            'tahun' => $tahun, 'tipe' => 'realisasi', 'is_triwulan' => 1, 'is_akumulasi' => 0,
        ])->keyBy(fn ($r) => $r->sub_golongan_kode . '-' . $r->bulan);

        $targetAkumDes = $this->allFiltered([
            'tahun' => $tahun, 'tipe' => 'target', 'is_triwulan' => 0, 'is_akumulasi' => 1, 'bulan' => 12,
        ])->keyBy('sub_golongan_kode');

        $realAkumDes = $this->allFiltered([
            'tahun' => $tahun, 'tipe' => 'realisasi', 'is_triwulan' => 0, 'is_akumulasi' => 1, 'bulan' => 12,
        ])->keyBy('sub_golongan_kode');

        foreach ($golonganMap as $kode => $label) {
            $rkapData[] = $this->buildRkapBaris(
                $label, $this->golonganJumlahKode[$kode],
                $targetTw, $realTw, $targetAkumDes, $realAkumDes
            );
        }

        $rkapData[] = $this->buildRkapBaris(
            'TOTAL', 'JUMLAH',
            $targetTw, $realTw, $targetAkumDes, $realAkumDes
        );

        return $rkapData;
    }

    protected function buildRkapBaris(
        string $label,
        string $kode,
        $targetTw,
        $realTw,
        $targetAkumDes,
        $realAkumDes
    ): array {
        $target = [];
        $real   = [];
        foreach ([1, 2, 3, 4] as $tw) {
            $target[$tw] = (float) ($targetTw->get($kode . '-' . $tw)->nilai_kva ?? 0);
            $real[$tw]   = (float) ($realTw->get($kode . '-' . $tw)->nilai_kva ?? 0);
        }

        $targetTotal = (float) ($targetAkumDes->get($kode)->nilai_kva ?? 0);
        $realTotal   = (float) ($realAkumDes->get($kode)->nilai_kva ?? 0);

        return [
            'golongan'      => $label,
            'targetTw1'     => $target[1],
            'targetTw2'     => $target[2],
            'targetTw3'     => $target[3],
            'targetTw4'     => $target[4],
            'targetTotal'   => $targetTotal,
            'realTw1'       => $real[1],
            'realTw2'       => $real[2],
            'realTw3'       => $real[3],
            'realTw4'       => $real[4],
            'realTotal'     => $realTotal,
            'pencapaianTw1' => $this->hitungPersentase($target[1], $real[1]),
            'pencapaianTw2' => $this->hitungPersentase($target[2], $real[2]),
            'pencapaianTw3' => $this->hitungPersentase($target[3], $real[3]),
            'pencapaianTw4' => $this->hitungPersentase($target[4], $real[4]),
            'pencapaian'    => $this->hitungPersentase($targetTotal, $realTotal),
        ];
    }

    // ============================================================
    // PERBANDINGAN TAHUN INI VS TAHUN LALU
    // ============================================================

    protected function buildPerbandingan(int $tahunIni, int $tahunLalu): array
    {
        $getSeries = function (int $tahun, string $tipe, int $akum) {
            $rows = $this->allFiltered([
                'tahun' => $tahun, 'tipe' => $tipe, 'is_triwulan' => 0, 'is_akumulasi' => $akum,
                'sub_golongan_kode' => 'JUMLAH',
            ]);
            $arr = array_fill(0, 12, 0);
            foreach ($rows as $row) {
                $arr[$row->bulan - 1] = (float) ($row->nilai_kva ?? 0);
            }
            return $arr;
        };

        $targetIni     = $getSeries($tahunIni, 'target', 0);
        $targetIniAkum = $getSeries($tahunIni, 'target', 1);
        $realIni       = $getSeries($tahunIni, 'realisasi', 0);
        $realIniAkum   = $getSeries($tahunIni, 'realisasi', 1);

        $targetLalu     = $getSeries($tahunLalu, 'target', 0);
        $targetLaluAkum = $getSeries($tahunLalu, 'target', 1);
        $realLalu       = $getSeries($tahunLalu, 'realisasi', 0);
        $realLaluAkum   = $getSeries($tahunLalu, 'realisasi', 1);

        $bulanNama = [
            1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL', 5 => 'MEI', 6 => 'JUNI',
            7 => 'JULI', 8 => 'AGUSTUS', 9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER',
        ];

        $result = [];
        for ($i = 0; $i < 12; $i++) {
            $deltaKva     = $realIni[$i] - $targetIni[$i];
            $deltaAkumKva = $realIniAkum[$i] - $targetIniAkum[$i];

            $result[] = [
                'bulan'               => $bulanNama[$i + 1],
                'target2024'          => $targetLalu[$i],
                'akum2024'            => $targetLaluAkum[$i],
                'realisasi2024'       => $realLalu[$i],
                'realisasiAkum2024'   => $realLaluAkum[$i],
                'target2025'          => $targetIni[$i],
                'akum2025'            => $targetIniAkum[$i],
                'realisasi2025'       => $realIni[$i],
                'realisasiAkum2025'   => $realIniAkum[$i],
                'deltaKva'            => $deltaKva,
                'deltaPct'            => $this->hitungPersentase($targetIni[$i], $deltaKva),
                'deltaAkumKva'        => $deltaAkumKva,
                'deltaAkumPct'        => $this->hitungPersentase($targetIniAkum[$i], $deltaAkumKva),
                'pencapaianTarget'    => $this->hitungPersentase($targetIniAkum[11], $targetIniAkum[$i]),
                'pencapaianRealisasi' => $this->hitungPersentase($targetIniAkum[11], $realIniAkum[$i]),
            ];
        }

        return $result;
    }

    // ============================================================
    // BUAT TAHUN & HAPUS TAHUN
    // ============================================================

    public function buatTahun(int $tahun, string $tipe): array
    {
        $kodeList = RtDimSubGolongan::orderBy('golongan_kode')->orderBy('urutan')->pluck('kode');
        $created  = 0;

        foreach ($kodeList as $kode) {
            $golonganKode = $this->resolveGolonganKode($kode);

            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $this->updateOrCreateRow(
                    [
                        'tahun' => $tahun, 'tipe' => $tipe, 'sub_golongan_kode' => $kode,
                        'bulan' => $bulan, 'is_triwulan' => 0, 'is_akumulasi' => 0,
                    ],
                    [
                        'nilai_kva' => 0, 'golongan_kode' => $golonganKode,
                        'sumber' => 'manual',
                    ]
                );
                $created++;
            }

            $this->hitungTriwulan($tahun, $tipe, $kode);
            $this->hitungAkumulasi($tahun, $tipe, $kode);
        }

        return ['tahun' => $tahun, 'tipe' => $tipe, 'rows_created' => $created];
    }

    public function hapusTahun(int $tahun, ?string $tipe = null): array
    {
        $filter = ['tahun' => $tahun];
        if ($tipe) {
            $filter['tipe'] = $tipe;
        }

        $deleted = $this->deleteFiltered($filter);

        return ['tahun' => $tahun, 'tipe' => $tipe ?? 'semua', 'rows_deleted' => $deleted];
    }
}