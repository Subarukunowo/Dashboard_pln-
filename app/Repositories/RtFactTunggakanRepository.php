<?php

namespace App\Repositories;

use App\Models\RtFactTunggakan;

class RtFactTunggakanRepository extends BaseRepository
{
    public function __construct(RtFactTunggakan $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters($query, array $filters)
    {
        return $query
            ->when(isset($filters['tahun']), fn ($q) => $q->where('tahun', $filters['tahun']))
            ->when(isset($filters['bulan']), fn ($q) => $q->where('bulan', $filters['bulan']))
            ->when(isset($filters['sumber']), fn ($q) => $q->where('sumber', $filters['sumber']));
    }
}