<?php

namespace App\Repositories\Contracts;

interface StatsRepositoryInterface
{
    public function getBppStats(int $tahun, int $bulan): array;
    public function getLossesStats(int $tahun, int $bulan): array;
    public function getPenjualanStats(int $tahun, int $bulan): array;
    public function getBabinStats(int $tahun, int $bulan): array;
    public function getJamNyalaStats(int $tahun, int $bulan): array;
    public function getGolTarifStats(int $tahun, int $bulan): array;
    public function getPendapatanStats(int $tahun, int $bulan): array;
    public function getRpkwhStats(int $tahun, int $bulan): array;
    public function getPelangganStats(int $tahun, int $bulan): array;
    public function getKvaStats(int $tahun, int $bulan): array;
    public function getTunggakanStats(int $tahun, int $bulan): array;
    public function getP2tlStats(int $tahun, int $bulan): array;
}