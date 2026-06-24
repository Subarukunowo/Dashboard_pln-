<?php

namespace App\Services;

use App\Repositories\Contracts\StatsRepositoryInterface;

class StatsService
{
    public function __construct(
        protected StatsRepositoryInterface $repository
    ) {}

    public function getStats(string $section, int $tahun, int $bulan): array
    {
        return match ($section) {
            'bpp'        => $this->repository->getBppStats($tahun, $bulan),
            'losses'     => $this->repository->getLossesStats($tahun, $bulan),
            'penjualan'  => $this->repository->getPenjualanStats($tahun, $bulan),
            'babin'      => $this->repository->getBabinStats($tahun, $bulan),
            'jamnyala'   => $this->repository->getJamNyalaStats($tahun, $bulan),
            'goltarif'   => $this->repository->getGolTarifStats($tahun, $bulan),
            'pendapatan' => $this->repository->getPendapatanStats($tahun, $bulan),
            'rpkwh'      => $this->repository->getRpkwhStats($tahun, $bulan),
            'pelanggan'  => $this->repository->getPelangganStats($tahun, $bulan),
            'kva'        => $this->repository->getKvaStats($tahun, $bulan),
            'tunggakan'  => $this->repository->getTunggakanStats($tahun, $bulan),
            'p2tl'       => $this->repository->getP2tlStats($tahun, $bulan),
            default      => [],
        };
    }
}