<?php

namespace App\Repositories;

use App\Models\RtFactHjr;
use Illuminate\Database\Eloquent\Collection;

class RtFactHjrRepository extends BaseRepository
{
    public function __construct(RtFactHjr $model)
    {
        parent::__construct($model);
    }

    public function getByTahun(int $tahun): Collection
    {
        return $this->model->newQuery()
            ->where('tahun', $tahun)
            ->where('is_akumulasi', 1)
            ->where('is_triwulan', 0)
            ->where('tipe', 'realisasi')
            ->get();
    }

    protected function applyFilters($query, array $filters)
    {
        return $query
            ->when(isset($filters['tahun']),        fn($q) => $q->where('tahun', $filters['tahun']))
            ->when(isset($filters['bulan']),        fn($q) => $q->where('bulan', $filters['bulan']))
            ->when(isset($filters['tipe']),         fn($q) => $q->where('tipe', $filters['tipe']))
            ->when(isset($filters['tarif_kode']),   fn($q) => $q->where('tarif_kode', $filters['tarif_kode']))
            ->when(isset($filters['tegangan']),     fn($q) => $q->where('tegangan', $filters['tegangan']))
            ->when(isset($filters['is_triwulan']),  fn($q) => $q->where('is_triwulan', $filters['is_triwulan']))
            ->when(isset($filters['is_akumulasi']), fn($q) => $q->where('is_akumulasi', $filters['is_akumulasi']))
            ->when(isset($filters['sumber']),       fn($q) => $q->where('sumber', $filters['sumber']));
    }
}