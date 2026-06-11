<?php

namespace App\Repositories\Concerns;

trait HasGolonganFilters
{
    protected function applyGolonganFilters($query, array $filters)
    {
        return $query
            ->when(isset($filters['golongan_kode']),     fn ($q) => $q->where('golongan_kode', $filters['golongan_kode']))
            ->when(isset($filters['sub_golongan_kode']), fn ($q) => $q->where('sub_golongan_kode', $filters['sub_golongan_kode']));
    }
}
