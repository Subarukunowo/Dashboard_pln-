<?php

namespace App\Services;

use App\Repositories\RtFactBppRepository;

class RtFactBppService extends BaseService
{
    public function __construct(RtFactBppRepository $repository)
    {
        parent::__construct($repository);
    }

    public function getAllChartData(int $tahun): array
    {
        $jenisList = ['pembangkit', 'transmisi', 'distribusi_tm', 'distribusi_tr'];

        $labelMap = [
            'pembangkit'    => 'BPP PEMBANGKIT',
            'transmisi'     => 'BPP S.D TRANSMISI',
            'distribusi_tm' => 'BPP S.D DISTRIBUSI TM',
            'distribusi_tr' => 'BPP S.D DISTRIBUSI TR',
        ];

        // Fallback ke tahun sebelumnya jika tidak ada data
        $hasData      = $this->repository->getModel()->where('tahun', $tahun)->exists();
        $tahunEfektif = $hasData ? $tahun : $tahun - 1;

        $allData = $this->repository->getModel()
            ->where('tahun', $tahunEfektif)
            ->get();

        $result = [];

        foreach ($jenisList as $jenis) {
            $filtered    = $allData->where('jenis_bpp', $jenis);
            $konsolidasi = [];
            $internal    = [];

            for ($m = 1; $m <= 12; $m++) {
                $rows = $filtered->where('bulan', $m);

                $kVal = $rows->firstWhere('versi', 'konsolidasi')?->nilai_rpkwh;
                $iVal = $rows->firstWhere('versi', 'internal')?->nilai_rpkwh;

                $konsolidasi[] = $kVal !== null ? (float) $kVal : null;
                $internal[]    = $iVal !== null ? (float) $iVal : null;
            }

            $result[$labelMap[$jenis]] = [
                'konsolidasi'  => $konsolidasi,
                'internal'     => $internal,
                'tahun_efektif'=> $tahunEfektif,
                'is_fallback'  => !$hasData,
            ];
        }

        return $result;
    }
}