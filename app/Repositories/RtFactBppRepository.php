<?php

namespace App\Repositories;

use App\Models\RtFactBpp;

class RtFactBppRepository extends BaseRepository
{
    public function __construct(RtFactBpp $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters($query, array $filters)
    {
        return $query
            ->when(isset($filters['tahun']),    fn ($q) => $q->where('tahun', $filters['tahun']))
            ->when(isset($filters['bulan']),    fn ($q) => $q->where('bulan', $filters['bulan']))
            ->when(isset($filters['versi']),    fn ($q) => $q->where('versi', $filters['versi']))
            ->when(isset($filters['jenis_bpp']), fn ($q) => $q->where('jenis_bpp', $filters['jenis_bpp']))
            ->when(isset($filters['sumber']),   fn ($q) => $q->where('sumber', $filters['sumber']));
    }
}
