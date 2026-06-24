<?php

namespace App\Repositories;

use App\Models\RtFactBabin;

class RtFactBabinRepository extends BaseRepository
{
    public function __construct(RtFactBabin $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters($query, array $filters)
    {
        return $query
            ->when(isset($filters['tahun']),        fn ($q) => $q->where('tahun', $filters['tahun']))
            ->when(isset($filters['bulan']),        fn ($q) => $q->where('bulan', $filters['bulan']))
            ->when(isset($filters['tipe']),         fn ($q) => $q->where('tipe', $filters['tipe']))
            ->when(isset($filters['area']),         fn ($q) => $q->where('area', $filters['area']))
            ->when(isset($filters['sumber']),       fn ($q) => $q->where('sumber', $filters['sumber']))
            ->when(isset($filters['is_akumulasi']), fn ($q) => $q->where('is_akumulasi', $filters['is_akumulasi']));
    }
    
}
