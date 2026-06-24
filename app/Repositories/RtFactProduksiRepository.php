<?php

namespace App\Repositories;

use App\Models\RtFactProduksi;

class RtFactProduksiRepository extends BaseRepository
{
    public function __construct(RtFactProduksi $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters($query, array $filters)
    {
        return $query
            ->when(isset($filters['tahun']), fn ($q) => $q->where('tahun', $filters['tahun']))
            ->when(isset($filters['bulan']), fn ($q) => $q->where('bulan', $filters['bulan']));
    }
}   