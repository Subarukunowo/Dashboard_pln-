<?php

namespace App\Services;

use App\Repositories\RtFactPenjualanRepository;

class RtFactPenjualanService extends BaseService
{
    public function __construct(RtFactPenjualanRepository $repository)
    {
        parent::__construct($repository);
    }

    public function getChartData(int $tahun): array
    {
        // Fallback ke tahun sebelumnya jika kosong
        $hasData      = $this->repository->all(['tahun' => $tahun, 'is_triwulan' => 0])->isNotEmpty();
        $tahunEfektif = $hasData ? $tahun : $tahun - 1;

        $allData = $this->repository->all(['tahun' => $tahunEfektif, 'is_triwulan' => 0]);

        $bulananTarget    = $allData->where('is_akumulasi', 0)->where('tipe', 'target')->groupBy('sub_golongan_kode');
        $bulananRealisasi = $allData->where('is_akumulasi', 0)->where('tipe', 'realisasi')->groupBy('sub_golongan_kode');
        $akumTarget       = $allData->where('is_akumulasi', 1)->where('tipe', 'target')->groupBy('sub_golongan_kode');
        $akumRealisasi    = $allData->where('is_akumulasi', 1)->where('tipe', 'realisasi')->groupBy('sub_golongan_kode');

        $allKodes = $bulananTarget->keys()->merge($bulananRealisasi->keys())->unique()->values();

        $perTarif = [];
        foreach ($allKodes as $kode) {
            $t  = $this->toMonthly($bulananTarget[$kode]    ?? collect(), 'nilai_kwh');
            $r  = $this->toMonthly($bulananRealisasi[$kode] ?? collect(), 'nilai_kwh');
            $ta = $this->toMonthly($akumTarget[$kode]       ?? collect(), 'nilai_kwh');
            $ra = $this->toMonthly($akumRealisasi[$kode]    ?? collect(), 'nilai_kwh');

            $perTarif[$kode] = [
                'target'        => $t,
                'realisasi'     => $r,
                'targetAkum'    => $ta,
                'realisasiAkum' => $ra,
            ];
        }

        // JUMLAH untuk view utama
        $jumlah = $perTarif['JUMLAH'] ?? [
            'target'        => array_fill(0, 12, 0),
            'realisasi'     => array_fill(0, 12, 0),
            'targetAkum'    => array_fill(0, 12, 0),
            'realisasiAkum' => array_fill(0, 12, 0),
        ];

        // % pencapaian bulanan
        $persenBulanan = array_map(
            fn($r, $t) => $t > 0 ? round($r / $t * 100, 2) : null,
            $jumlah['realisasi'], $jumlah['target']
        );

        // % pencapaian akumulasi
        $persenAkumulasi = array_map(
            fn($r, $t) => $t > 0 ? round($r / $t * 100, 2) : null,
            $jumlah['realisasiAkum'], $jumlah['targetAkum']
        );

        return [
            'tahun_efektif'   => $tahunEfektif,
            'is_fallback'     => !$hasData,
            'bulanan'         => $jumlah,
            'persenBulanan'   => $persenBulanan,
            'akumulasi'       => ['target' => $jumlah['targetAkum'], 'realisasi' => $jumlah['realisasiAkum']],
            'persenAkumulasi' => $persenAkumulasi,
            'perTarif'        => $perTarif,
            'tarifList'       => $allKodes->values()->all(),
        ];
    }

    private function toMonthly($rows, string $field): array
    {
        $result = array_fill(0, 12, null);
        foreach ($rows as $row) {
            $result[$row->bulan - 1] = (float) $row->$field;
        }
        return $result;
    }
}