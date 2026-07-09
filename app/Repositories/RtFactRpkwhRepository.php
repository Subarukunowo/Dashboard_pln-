<?php

namespace App\Repositories;

use App\Models\RtFactRpkwh;

class RtFactRpkwhRepository extends BaseRepository
{
    public function __construct(RtFactRpkwh $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters($query, array $filters)
    {
        $where = fn ($q, $col, $val) => is_array($val) ? $q->whereIn($col, $val) : $q->where($col, $val);

        return $query
            ->when(isset($filters['tahun']),             fn ($q) => $where($q, 'tahun', $filters['tahun']))
            ->when(isset($filters['bulan']),              fn ($q) => $where($q, 'bulan', $filters['bulan']))
            ->when(isset($filters['tipe']),                fn ($q) => $where($q, 'tipe', $filters['tipe']))
            ->when(isset($filters['sumber']),              fn ($q) => $where($q, 'sumber', $filters['sumber']))
            ->when(isset($filters['golongan_kode']),       fn ($q) => $where($q, 'golongan_kode', $filters['golongan_kode']))
            ->when(isset($filters['sub_golongan_kode']),   fn ($q) => $where($q, 'sub_golongan_kode', $filters['sub_golongan_kode']))
            ->when(isset($filters['is_triwulan']),         fn ($q) => $where($q, 'is_triwulan', $filters['is_triwulan']))
            ->when(isset($filters['is_akumulasi']),        fn ($q) => $where($q, 'is_akumulasi', $filters['is_akumulasi']));
    }
}