<?php

namespace App\Repositories;

use App\Models\RtDimSubGolongan;

class RtDimSubGolonganRepository extends BaseRepository
{
    public function __construct(RtDimSubGolongan $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters($query, array $filters)
    {
        return $query
            ->when(isset($filters['golongan_kode']), fn ($q) => $q->where('golongan_kode', $filters['golongan_kode']))
            ->when(isset($filters['search']),        fn ($q) => $q->where('nama', 'like', '%' . addcslashes($filters['search'], '%_\\') . '%'));
    }
}
