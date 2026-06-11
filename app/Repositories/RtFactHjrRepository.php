<?php

namespace App\Repositories;

use App\Models\RtFactHjr;
use App\Repositories\Concerns\HasCommonFactFilters;

class RtFactHjrRepository extends BaseRepository
{
    use HasCommonFactFilters;

    public function __construct(RtFactHjr $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters($query, array $filters)
    {
        $query = parent::applyFilters($query, $filters);

        return $query
            ->when(isset($filters['tarif_kode']), fn ($q) => $q->where('tarif_kode', $filters['tarif_kode']))
            ->when(isset($filters['tegangan']),   fn ($q) => $q->where('tegangan', $filters['tegangan']));
    }
}
