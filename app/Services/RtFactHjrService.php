<?php

namespace App\Services;

use App\Repositories\RtFactHjrRepository;

class RtFactHjrService extends BaseService
{
    public function __construct(RtFactHjrRepository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Rata-rata HJR per bulan, dikelompokkan per tegangan (TR / TM).
     * Dipakai untuk overlay chart BPP vs HJR.
     */
    public function getAvgByTegangan(int $tahun): array
    {
              $allData = $this->repository->all()->where('tahun', $tahun);

        $result = [];

        foreach (['TR', 'TM'] as $tegangan) {
            $filtered = $allData->where('tegangan', $tegangan);
            $monthly  = [];

            for ($m = 1; $m <= 12; $m++) {
                $rows = $filtered->where('bulan', $m);
                $avg  = $rows->isNotEmpty()
                    ? round($rows->avg('nilai_rpkwh'), 2)
                    : null;

                $monthly[] = $avg;
            }

            $result[$tegangan] = $monthly;
        }

        return $result;
    }
}