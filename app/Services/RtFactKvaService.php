<?php

namespace App\Services;

use App\Repositories\RtFactPelangganRepository;

class RtFactKvaService extends BaseService
{
    protected array $golonganList = ['S', 'R', 'B', 'I', 'P', 'M'];
    protected array $golonganJumlahKode = [
        'S' => 'JUMLAH-S', 'R' => 'JUMLAH-R', 'B' => 'JUMLAH-B',
        'I' => 'JUMLAH-I', 'P' => 'JUMLAH-P', 'M' => 'JUMLAH-M',
    ];

    public function __construct( RtFactPelangganRepository $repository)
    {
        parent::__construct($repository);
    }

    public function getChartData(int $tahun, ?int $tahunLalu = null): array
    {
        $cek = $this->repository->all([
            'tahun' => $tahun, 'tipe' => 'realisasi', 'is_triwulan' => 0, 'is_akumulasi' => 0,
        ]);
        if ($cek->isEmpty()) {
            $tahun = $tahun - 1;
        }
        $tahunLalu = $tahunLalu ?? $tahun - 1;

        // 1. Rencana & Realisasi per sub_golongan_kode pakai nilai_kva
        $rencanaRows = $this->repository->all([
            'tahun' => $tahun, 'tipe' => 'target', 'is_triwulan' => 0, 'is_akumulasi' => 0,
        ]);
        $realisasiRows = $this->repository->all([
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

        // 2. Target & realisasi golongan akumulasi (KVA)
        $targetGolRows = $this->repository->all([
            'tahun' => $tahun, 'tipe' => 'target', 'is_triwulan' => 0, 'is_akumulasi' => 1,
        ]);
        $realisasiGolRowsLalu = $this->repository->all([
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

        // 3. RKAP KVA
        $rkapData = $this->buildRkapKva($tahun);

        // 4. Perbandingan KVA tahun ini vs tahun lalu (per bulan, golongan TOTAL/JUMLAH)
        $perbandinganData = $this->buildPerbandingan($tahun, $tahunLalu);

        return [
            'tarif'             => $tarif,
            'golongan'          => $golongan,
            'rencanaData'       => $rencanaData,
            'realisasiData'     => $realisasiData,
            'targetGolAkum'     => $targetGolAkum,
            'realisasiGolAkum'  => $realisasiGolAkum,
            'rkapData'          => $rkapData,
            'perbandinganData'  => $perbandinganData,
            'tahun_efektif'     => $tahun,
        ];
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

    protected function buildRkapKva(int $tahun): array
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
                $target[$tw] = (float) ($targetAkum->get($jumlahKode . '-' . $bln)->nilai_kva ?? 0);
                $real[$tw]   = (float) ($realAkum->get($jumlahKode . '-' . $bln)->nilai_kva ?? 0);
            }

            // TW bukan akumulasi murni, jadi hitung selisih antar TW untuk nilai per-TW
            $targetPerTw = [
                1 => $target[1],
                2 => $target[2] - $target[1],
                3 => $target[3] - $target[2],
                4 => $target[4] - $target[3],
            ];
            $realPerTw = [
                1 => $real[1],
                2 => $real[2] - $real[1],
                3 => $real[3] - $real[2],
                4 => $real[4] - $real[3],
            ];

            $targetTotal = $target[4];
            $realTotal   = $real[4];

            $rkapData[] = [
                'golongan'    => $label,
                'targetTw1'   => $targetPerTw[1],
                'targetTw2'   => $targetPerTw[2],
                'targetTw3'   => $targetPerTw[3],
                'targetTw4'   => $targetPerTw[4],
                'targetTotal' => $targetTotal,
                'realTw1'     => $realPerTw[1],
                'realTw2'     => $realPerTw[2],
                'realTw3'     => $realPerTw[3],
                'realTw4'     => $realPerTw[4],
                'realTotal'   => $realTotal,
                'pencapaian'  => $targetTotal > 0 ? $realTotal / $targetTotal : 0,
            ];
        }

        // Baris TOTAL
        $targetAll = [];
        $realAll   = [];
        foreach ($twBulan as $tw => $bln) {
            $targetAll[$tw] = (float) ($targetAkum->get('JUMLAH-' . $bln)->nilai_kva ?? 0);
            $realAll[$tw]   = (float) ($realAkum->get('JUMLAH-' . $bln)->nilai_kva ?? 0);
        }
        $targetAllPerTw = [
            1 => $targetAll[1],
            2 => $targetAll[2] - $targetAll[1],
            3 => $targetAll[3] - $targetAll[2],
            4 => $targetAll[4] - $targetAll[3],
        ];
        $realAllPerTw = [
            1 => $realAll[1],
            2 => $realAll[2] - $realAll[1],
            3 => $realAll[3] - $realAll[2],
            4 => $realAll[4] - $realAll[3],
        ];
        $targetTotalAll = $targetAll[4];
        $realTotalAll   = $realAll[4];

        $rkapData[] = [
            'golongan'    => 'TOTAL',
            'targetTw1'   => $targetAllPerTw[1],
            'targetTw2'   => $targetAllPerTw[2],
            'targetTw3'   => $targetAllPerTw[3],
            'targetTw4'   => $targetAllPerTw[4],
            'targetTotal' => $targetTotalAll,
            'realTw1'     => $realAllPerTw[1],
            'realTw2'     => $realAllPerTw[2],
            'realTw3'     => $realAllPerTw[3],
            'realTw4'     => $realAllPerTw[4],
            'realTotal'   => $realTotalAll,
            'pencapaian'  => $targetTotalAll > 0 ? $realTotalAll / $targetTotalAll : 0,
        ];

        return $rkapData;
    }

    protected function buildPerbandingan(int $tahunIni, int $tahunLalu): array
    {
        // Target & realisasi bulanan + akumulasi untuk golongan TOTAL (JUMLAH), kedua tahun
        $getSeries = function (int $tahun, string $tipe, int $akum) {
            $rows = $this->repository->all([
                'tahun' => $tahun, 'tipe' => $tipe, 'is_triwulan' => 0, 'is_akumulasi' => $akum,
                'sub_golongan_kode' => 'JUMLAH',
            ]);
            $arr = array_fill(0, 12, 0);
            foreach ($rows as $row) {
                $arr[$row->bulan - 1] = (float) ($row->nilai_kva ?? 0);
            }
            return $arr;
        };

        $targetIni      = $getSeries($tahunIni, 'target', 0);
        $targetIniAkum  = $getSeries($tahunIni, 'target', 1);
        $realIni        = $getSeries($tahunIni, 'realisasi', 0);
        $realIniAkum    = $getSeries($tahunIni, 'realisasi', 1);

        $targetLalu      = $getSeries($tahunLalu, 'target', 0);
        $targetLaluAkum  = $getSeries($tahunLalu, 'target', 1);
        $realLalu        = $getSeries($tahunLalu, 'realisasi', 0);
        $realLaluAkum    = $getSeries($tahunLalu, 'realisasi', 1);

        $bulanNama = [
            1=>'JANUARI',2=>'FEBRUARI',3=>'MARET',4=>'APRIL',5=>'MEI',6=>'JUNI',
            7=>'JULI',8=>'AGUSTUS',9=>'SEPTEMBER',10=>'OKTOBER',11=>'NOVEMBER',12=>'DESEMBER',
        ];

        $result = [];
        for ($i = 0; $i < 12; $i++) {
            $deltaKva     = $realIni[$i] - $targetIni[$i];
            $deltaPct     = $targetIni[$i] != 0 ? $deltaKva / $targetIni[$i] : 0;
            $deltaAkumKva = $realIniAkum[$i] - $targetIniAkum[$i];
            $deltaAkumPct = $targetIniAkum[$i] != 0 ? $deltaAkumKva / $targetIniAkum[$i] : 0;

            $result[] = [
                'bulan'              => $bulanNama[$i + 1],
                'target2024'         => $targetLalu[$i],
                'akum2024'           => $targetLaluAkum[$i],
                'realisasi2024'      => $realLalu[$i],
                'realisasiAkum2024'  => $realLaluAkum[$i],
                'target2025'         => $targetIni[$i],
                'akum2025'           => $targetIniAkum[$i],
                'realisasi2025'      => $realIni[$i],
                'realisasiAkum2025'  => $realIniAkum[$i],
                'deltaKva'           => $deltaKva,
                'deltaPct'           => $deltaPct,
                'deltaAkumKva'       => $deltaAkumKva,
                'deltaAkumPct'       => $deltaAkumPct,
                'pencapaianTarget'   => $targetLalu[$i] != 0 ? $targetIni[$i] / $targetLalu[$i] : 0,
                'pencapaianRealisasi'=> $realLalu[$i] != 0 ? $realIni[$i] / $realLalu[$i] : 0,
            ];
        }

        return $result;
    }
}