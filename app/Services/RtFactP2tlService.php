<?php

namespace App\Services;

use App\Repositories\RtFactP2tlRepository;

class RtFactP2tlService extends BaseService
{
    public function __construct( RtFactP2tlRepository $repository)
    {
        parent::__construct($repository);
    }

    public function getChartData(int $tahun, string $tipe = 'realisasi'): array
    {
        $rows = $this->repository->all([
            'tahun'        => $tahun,
            'tipe'         => $tipe,
            'is_triwulan'  => 0,
            'is_akumulasi' => 0,
        ])->sortBy('bulan');

        // Fallback ke tahun sebelumnya jika tidak ada data
        $tahunEfektif = $tahun;
        $isFallback   = false;

        if ($rows->isEmpty()) {
            $tahunEfektif = $tahun - 1;
            $isFallback   = true;
            $rows = $this->repository->all([
                'tahun'        => $tahunEfektif,
                'tipe'         => $tipe,
                'is_triwulan'  => 0,
                'is_akumulasi' => 0,
            ])->sortBy('bulan');
        }

        $bulanNama = [
            1=>'JANUARI', 2=>'FEBRUARI', 3=>'MARET', 4=>'APRIL',
            5=>'MEI', 6=>'JUNI', 7=>'JULI', 8=>'AGUSTUS',
            9=>'SEPTEMBER', 10=>'OKTOBER', 11=>'NOVEMBER', 12=>'DESEMBER',
        ];

        $data = $rows->values()->map(fn ($r, $i) => [
            'no'              => $i + 1,
            'bulan'           => $bulanNama[$r->bulan] ?? $r->bulan,
            'pemeriksaan'     => (int) $r->jumlah_pemeriksaan,
            'temuanPelanggan' => (int) $r->jumlah_temuan,
            'temuanKwh'       => (float) $r->temuan_kwh,
            'tagsus'          => (float) $r->nilai_tagihan_rp,
        ])->values()->all();

        return [
            'data'          => $data,
            'tahun_efektif' => $tahunEfektif,
            'is_fallback'   => $isFallback,
        ];
    }
}