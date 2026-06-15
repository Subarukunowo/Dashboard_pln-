<?php

namespace App\Repositories;

use App\Models\RtFactJamNyala;

class RtFactJamNyalaRepository extends BaseRepository
{
    public function __construct(RtFactJamNyala $model)  
    {
        parent::__construct($model);
    }

    protected function applyFilters($query, array $filters)
    {
        return $query
            ->when(isset($filters['tahun']),         fn($q) => $q->where('tahun', $filters['tahun']))
            ->when(isset($filters['bulan']),         fn($q) => $q->where('bulan', $filters['bulan']))
            ->when(isset($filters['tipe']),          fn($q) => $q->where('tipe', $filters['tipe']))
            ->when(isset($filters['golongan_kode']), fn($q) => $q->where('golongan_kode', $filters['golongan_kode']))
            ->when(isset($filters['is_triwulan']),   fn($q) => $q->where('is_triwulan', $filters['is_triwulan']))
            ->when(isset($filters['is_akumulasi']),  fn($q) => $q->where('is_akumulasi', $filters['is_akumulasi']))
            ->when(isset($filters['sumber']),        fn($q) => $q->where('sumber', $filters['sumber']));
    }
}