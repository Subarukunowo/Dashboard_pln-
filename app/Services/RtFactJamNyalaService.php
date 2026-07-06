<?php

namespace App\Services;

use App\Repositories\RtFactJamNyalaRepository;
use Illuminate\Support\Collection;

class RtFactJamNyalaService extends BaseService
{
     public function __construct( RtFactJamNyalaRepository $repository)
    {
        parent::__construct($repository);
    }
    /**
     * Ambil data terformat untuk chart — pivot per golongan_kode ke 12 bulan.
     * Dipakai oleh endpoint khusus chart agar JS tidak perlu pivot sendiri.
     *
     * @return array{ tarif: string[], data: array<string, float[]> }
     */
    public function getChartData(int $tahun, string $tipe = 'realisasi'): array
    {
        $rows = $this->repository->all([
            'tahun'        => $tahun,
            'tipe'         => $tipe,
            'is_triwulan'  => 0,
            'is_akumulasi' => 0,
        ]);

        // Fallback ke tahun sebelumnya jika tidak ada data
        $tahunEfektif = $tahun;
        if ($rows->isEmpty()) {
            $tahunEfektif = $tahun - 1;
            $rows = $this->repository->all([
                'tahun'        => $tahunEfektif,
                'tipe'         => $tipe,
                'is_triwulan'  => 0,
                'is_akumulasi' => 0,
            ]);
        }

        $rows = $rows->sortBy(['golongan_kode', 'bulan']);
        $tarif = [];
        $data  = [];

        foreach ($rows as $row) {
            $kode = $row->golongan_kode;
            if (!isset($data[$kode])) {
                $tarif[]     = $kode;
                $data[$kode] = array_fill(0, 12, null);
            }
            $data[$kode][$row->bulan - 1] = $row->nilai_jam !== null
                ? (float) $row->nilai_jam
                : null;
        }

        return compact('tarif', 'data') + ['tahun_efektif' => $tahunEfektif];
    }
}