<?php

namespace App\Services;

use App\Repositories\RtFactPelangganRepository;

class RtFactPelangganService extends BaseService
{
    protected array $golonganList = ['S', 'R', 'B', 'I', 'P', 'M'];
    protected array $golonganJumlahKode = [
        'S' => 'JUMLAH-S', 'R' => 'JUMLAH-R', 'B' => 'JUMLAH-B',
        'I' => 'JUMLAH-I', 'P' => 'JUMLAH-P', 'M' => 'JUMLAH-M',
    ];

    public function __construct(RtFactPelangganRepository $repository)
    {
        parent::__construct($repository);
    }

    public function getChartData(int $tahun, ?int $tahunLalu = null): array
    {
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
            $key = 'Jumlah ' . $this->golonganNama($g) . ' (' . $g . ')';
            $targetGolAkum[$key]    = $targetGolAkum[$key]    ?? array_fill(0, 12, 0);
            $realisasiGolAkum[$key] = $realisasiGolAkum[$key] ?? array_fill(0, 12, 0);
        }

        $golongan = array_map(
            fn ($g) => 'Jumlah ' . $this->golonganNama($g) . ' (' . $g . ')',
            $this->golonganList
        );

        // 3. RKAP — per golongan TW1-4 target/realisasi akumulasi
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
        ];
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
            if (!isset($map[$kode])) continue;

            $g   = $map[$kode];
            $key = 'Jumlah ' . $this->golonganNama($g) . ' (' . $g . ')';

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

    protected function buildRkapData(int $tahun): array
    {
        $rkapData = [];

        $golonganMap = [
            'S' => 'Sosial (S)', 'R' => 'Rumah Tangga (R)', 'B' => 'Bisnis (B)',
            'I' => 'Industri (I)', 'P' => 'Publik (P)', 'M' => 'Multiguna (M)',
        ];

        $targetAkum = $this->repository->all([
            'tahun' => $tahun, 'tipe' => 'target', 'is_triwulan' => 0, 'is_akumulasi' => 1,
        ])->keyBy(fn ($r) => $r->sub_golongan_kode . '-' . $r->bulan);

        $realAkum = $this->repository->all([
            'tahun' => $tahun, 'tipe' => 'realisasi', 'is_triwulan' => 0, 'is_akumulasi' => 1,
        ])->keyBy(fn ($r) => $r->sub_golongan_kode . '-' . $r->bulan);

        $twBulan = [1 => 3, 2 => 6, 3 => 9, 4 => 12];

        foreach ($golonganMap as $kode => $label) {
            $jumlahKode = $this->golonganJumlahKode[$kode];

            $target = [];
            $real   = [];
            foreach ($twBulan as $tw => $bln) {
                $target[$tw] = (float) ($targetAkum->get($jumlahKode . '-' . $bln)->nilai_pelanggan ?? 0);
                $real[$tw]   = (float) ($realAkum->get($jumlahKode . '-' . $bln)->nilai_pelanggan ?? 0);
            }

            $targetTotal = $target[4];
            $realTotal   = $real[4];

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

        $targetAll = [];
        $realAll   = [];
        foreach ($twBulan as $tw => $bln) {
            $targetAll[$tw] = (float) ($targetAkum->get('JUMLAH-' . $bln)->nilai_pelanggan ?? 0);
            $realAll[$tw]   = (float) ($realAkum->get('JUMLAH-' . $bln)->nilai_pelanggan ?? 0);
        }
        $targetTotalAll = $targetAll[4];
        $realTotalAll   = $realAll[4];

        $rkapData[] = [
            'golongan'    => 'JUMLAH',
            'targetTw1'   => $targetAll[1],
            'targetTw2'   => $targetAll[2],
            'targetTw3'   => $targetAll[3],
            'targetTw4'   => $targetAll[4],
            'targetTotal' => $targetTotalAll,
            'realTw1'     => $realAll[1],
            'realTw2'     => $realAll[2],
            'realTw3'     => $realAll[3],
            'realTw4'     => $realAll[4],
            'realTotal'   => $realTotalAll,
            'pencapaian'  => $targetTotalAll > 0 ? $realTotalAll / $targetTotalAll : 0,
        ];

        return $rkapData;
    }
}