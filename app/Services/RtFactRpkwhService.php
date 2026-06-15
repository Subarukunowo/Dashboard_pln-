<?php

namespace App\Services;

use App\Repositories\RtFactRpkwhRepository;

class RtFactRpkwhService extends BaseService
{
    protected array $golonganList = ['S', 'R', 'B', 'I', 'P', 'M'];
    protected array $golonganJumlahKode = [
        'S' => 'JUMLAH-S', 'R' => 'JUMLAH-R', 'B' => 'JUMLAH-B',
        'I' => 'JUMLAH-I', 'P' => 'JUMLAH-P', 'M' => 'JUMLAH-M',
    ];

    public function __construct(RtFactRpkwhRepository $repository)
    {
        parent::__construct($repository);
    }

    public function getChartData(int $tahun, ?int $tahunLalu = null): array
    {
        // Fallback ke tahun sebelumnya jika belum ada data realisasi tahun ini
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

        $rencanaData   = $this->pivot($rencanaRows);
        $realisasiData = $this->pivot($realisasiRows);

        $tarif = array_unique(array_merge(array_keys($rencanaData), array_keys($realisasiData)));
        sort($tarif);
        foreach ($tarif as $t) {
            $rencanaData[$t]   = $rencanaData[$t]   ?? array_fill(0, 12, 0);
            $realisasiData[$t] = $realisasiData[$t] ?? array_fill(0, 12, 0);
        }

        // 2. Target & Realisasi Golongan — akumulasi & bulanan
        $targetGolAkumRows = $this->repository->all([
            'tahun' => $tahun, 'tipe' => 'target', 'is_triwulan' => 0, 'is_akumulasi' => 1,
        ]);
        $targetGolBulRows = $this->repository->all([
            'tahun' => $tahun, 'tipe' => 'target', 'is_triwulan' => 0, 'is_akumulasi' => 0,
        ]);
        $realisasiGolAkumRows = $this->repository->all([
            'tahun' => $tahun, 'tipe' => 'realisasi', 'is_triwulan' => 0, 'is_akumulasi' => 1,
        ]);
        $realisasiGolBulRows = $this->repository->all([
            'tahun' => $tahun, 'tipe' => 'realisasi', 'is_triwulan' => 0, 'is_akumulasi' => 0,
        ]);

        $targetGolAkum    = $this->pivotGolongan($targetGolAkumRows);
        $targetGolBul     = $this->pivotGolongan($targetGolBulRows);
        $realisasiGolAkum = $this->pivotGolongan($realisasiGolAkumRows);
        $realisasiGolBul  = $this->pivotGolongan($realisasiGolBulRows);

        $golongan = array_map(
            fn ($g) => $this->golonganNama($g) . ' (' . $g . ')',
            $this->golonganList
        );

        foreach ($golongan as $key) {
            $targetGolAkum[$key]    = $targetGolAkum[$key]    ?? array_fill(0, 12, 0);
            $targetGolBul[$key]     = $targetGolBul[$key]     ?? array_fill(0, 12, 0);
            $realisasiGolAkum[$key] = $realisasiGolAkum[$key] ?? array_fill(0, 12, 0);
            $realisasiGolBul[$key]  = $realisasiGolBul[$key]  ?? array_fill(0, 12, 0);
        }

        // 3. Growth — belum ada sumber data di tabel ini, isi kosong
        $growthAkum = [];
        $growthBul  = [];
        foreach ($golongan as $key) {
            $growthAkum[$key] = array_fill(0, 12, 0);
            $growthBul[$key]  = array_fill(0, 12, 0);
        }

        // 4. BPP & HJR — belum ada sumber data, isi kosong
        $bppData = [];
        $hjrData = [];

        // 5. Analisa — JUMLAH (TOTAL) tahun ini vs tahun lalu
        $analisaData = $this->buildAnalisa($tahun, $tahunLalu);

        // 6. %Realisasi — pct_pencapaian dari golongan TOTAL bulanan
        $persenData = $this->buildPersen($tahun);

        return [
            'tarif'            => $tarif,
            'golongan'         => $golongan,
            'rencanaData'      => $rencanaData,
            'realisasiData'    => $realisasiData,
            'targetGolAkum'    => $targetGolAkum,
            'targetGolBul'     => $targetGolBul,
            'realisasiGolAkum' => $realisasiGolAkum,
            'realisasiGolBul'  => $realisasiGolBul,
            'growthAkum'       => $growthAkum,
            'growthBul'        => $growthBul,
            'bppData'          => $bppData,
            'hjrData'          => $hjrData,
            'analisaData'      => $analisaData,
            'persenData'       => $persenData,
            'tahun_efektif'    => $tahun,
        ];
    }

    protected function pivot($rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $key = $row->sub_golongan_kode;
            if (!isset($result[$key])) {
                $result[$key] = array_fill(0, 12, 0);
            }
            $result[$key][$row->bulan - 1] = (float) $row->nilai_rpkwh;
        }
        return $result;
    }

    protected function pivotGolongan($rows): array
    {
        $result = [];
        $map = array_flip($this->golonganJumlahKode);

        foreach ($rows as $row) {
            $kode = $row->sub_golongan_kode;
            if (!isset($map[$kode])) continue;

            $g   = $map[$kode];
            $key = $this->golonganNama($g) . ' (' . $g . ')';

            if (!isset($result[$key])) {
                $result[$key] = array_fill(0, 12, 0);
            }
            $result[$key][$row->bulan - 1] = (float) $row->nilai_rpkwh;
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

    protected function buildAnalisa(int $tahunIni, int $tahunLalu): array
    {
        $getSeries = function (int $tahun, string $tipe, int $akum) {
            $rows = $this->repository->all([
                'tahun' => $tahun, 'tipe' => $tipe, 'is_triwulan' => 0, 'is_akumulasi' => $akum,
                'sub_golongan_kode' => 'JUMLAH',
            ]);
            $arr = array_fill(0, 12, 0);
            foreach ($rows as $row) {
                $arr[$row->bulan - 1] = (float) $row->nilai_rpkwh;
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
            1=>'JANUARI',2=>'FEBRUARI',3=>'MARET',4=>'APRIL',5=>'MEI',6=>'JUNI',
            7=>'JULI',8=>'AGUSTUS',9=>'SEPTEMBER',10=>'OKTOBER',11=>'NOVEMBER',12=>'DESEMBER',
        ];

        $result = [];
        for ($i = 0; $i < 12; $i++) {
            $deltaRpkwh   = $realIni[$i] - $targetIni[$i];
            $deltaPct     = $targetIni[$i] != 0 ? $realIni[$i] / $targetIni[$i] : 0;
            $deltaAkum    = $realIniAkum[$i] - $targetIniAkum[$i];
            $deltaAkumPct = $targetIniAkum[$i] != 0 ? $realIniAkum[$i] / $targetIniAkum[$i] : 0;

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
                'deltaRpkwh'         => $deltaRpkwh,
                'deltaPct'           => $deltaPct,
                'deltaAkum'          => $deltaAkum,
                'deltaAkumPct'       => $deltaAkumPct,
                'pencapaianTarget'   => $targetLalu[$i] != 0 ? $targetIni[$i] / $targetLalu[$i] : 0,
                'pencapaianRealisasi'=> $realLalu[$i] != 0 ? $realIni[$i] / $realLalu[$i] : 0,
            ];
        }

        return $result;
    }

    protected function buildPersen(int $tahun): array
    {
        $rows = $this->repository->all([
            'tahun' => $tahun, 'is_triwulan' => 0, 'is_akumulasi' => 0,
            'sub_golongan_kode' => 'JUMLAH',
        ]);

        $bulanNama = [
            1=>'JANUARI',2=>'FEBRUARI',3=>'MARET',4=>'APRIL',5=>'MEI',6=>'JUNI',
            7=>'JULI',8=>'AGUSTUS',9=>'SEPTEMBER',10=>'OKTOBER',11=>'NOVEMBER',12=>'DESEMBER',
        ];

        $target = array_fill(0, 12, 0);
        $real   = array_fill(0, 12, 0);

        foreach ($rows as $row) {
            if ($row->tipe === 'target')    $target[$row->bulan - 1] = (float) $row->nilai_rpkwh;
            if ($row->tipe === 'realisasi') $real[$row->bulan - 1]   = (float) $row->nilai_rpkwh;
        }

        $result = [];
        for ($i = 0; $i < 12; $i++) {
            $result[] = [
                'bulan'     => $bulanNama[$i + 1],
                'target'    => $target[$i],
                'realisasi' => $real[$i],
                'delta'     => $real[$i] - $target[$i],
            ];
        }

        return $result;
    }
}