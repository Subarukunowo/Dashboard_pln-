<?php

namespace App\Services;

use App\Repositories\RtFactTunggakanRepository;

class RtFactTunggakanService extends BaseService
{
    const RATIO_TARGET = 0.0001; // 0,01% — sesuai data Excel

    public function __construct( RtFactTunggakanRepository $repository)
    {
        parent::__construct($repository);
    }

    public function getChartData(int $tahun): array
    {
        $rows = $this->repository->all(['tahun' => $tahun])->sortBy('bulan');

        $tahunEfektif = $tahun;
        $isFallback   = false;

        if ($rows->isEmpty()) {
            $tahunEfektif = $tahun - 1;
            $isFallback   = true;
            $rows = $this->repository->all(['tahun' => $tahunEfektif])->sortBy('bulan');
        }

        $bulanNama = [
            1=>'JANUARI',2=>'FEBRUARI',3=>'MARET',4=>'APRIL',5=>'MEI',6=>'JUNI',
            7=>'JULI',8=>'AGUSTUS',9=>'SEPTEMBER',10=>'OKTOBER',11=>'NOVEMBER',12=>'DESEMBER',
        ];

$data = $rows->values()->map(fn ($r) => [
    'id'              => $r->id,
    'bulan'           => $bulanNama[$r->bulan] ?? $r->bulan,
    'palBerjalan'     => (float) $r->pal_berjalan,
    'palTunggakan'    => (float) $r->pal_tunggakan,
    'tsBerjalan'      => (float) $r->ts_berjalan,
    'tsTunggakan'     => (float) $r->ts_tunggakan,
    'jumlahTunggakan' => (float) $r->jumlah_tunggakan,
    'pendapatan'      => (float) $r->pendapatan,
    'ratioTunggakan'  => (float) $r->ratio_tunggakan_pct / 100,
    'ratioTarget'     => self::RATIO_TARGET,
])->values()->all();

        return [
            'data'          => $data,
            'tahun_efektif' => $tahunEfektif,
            'is_fallback'   => $isFallback,
        ];
    }
}