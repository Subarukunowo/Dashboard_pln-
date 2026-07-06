<?php

namespace App\Services;

use App\Repositories\RtFactHjrRepository;
use App\Models\RtFactPendapatan;
use App\Models\RtFactPenjualan;

class RtFactHjrService extends BaseService
{
     public function __construct(RtFactHjrRepository $repository)
    {
        parent::__construct($repository);
    }
    /**
     * Rata-rata HJR per bulan, dikelompokkan per tegangan (TR / TM).
     * Fallback ke tahun sebelumnya jika data tahun ini kosong.
     */
    public function getAvgByTegangan(int $tahun): array
    {
        $model = $this->repository->getModel();

        // Fallback ke tahun sebelumnya jika kosong
        $hasData      = $model->where('tahun', $tahun)->exists();
        $tahunEfektif = $hasData ? $tahun : $tahun - 1;

        $allData = $model->where('tahun', $tahunEfektif)->get();

        $result = ['tahun_efektif' => $tahunEfektif, 'is_fallback' => !$hasData];

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

    /**
     * Hitung HJR otomatis per bulan dari:
     *   HJR[bulan] = pendapatan_realisasi_JUMLAH[bulan] / penjualan_realisasi_JUMLAH[bulan]
     *
     * Pendapatan dalam satuan Rupiah, Penjualan dalam kWh → hasil Rp/kWh.
     *
     * @return array ['calculated' => float[12], 'tahun_efektif' => int]
     */
    public function getCalculated(int $tahun): array
    {
        // Ambil realisasi pendapatan JUMLAH per bulan
        $pendapatan = RtFactPendapatan::where('tahun', $tahun)
            ->where('sub_golongan_kode', 'JUMLAH')
            ->where('tipe', 'realisasi')
            ->where('is_akumulasi', 0)
            ->where('is_triwulan', 0)
            ->pluck('nilai_rp', 'bulan');

        // Fallback tahun sebelumnya jika kosong
        $tahunEfektif = $pendapatan->isNotEmpty() ? $tahun : $tahun - 1;
        if ($tahunEfektif !== $tahun) {
            $pendapatan = RtFactPendapatan::where('tahun', $tahunEfektif)
                ->where('sub_golongan_kode', 'JUMLAH')
                ->where('tipe', 'realisasi')
                ->where('is_akumulasi', 0)
                ->where('is_triwulan', 0)
                ->pluck('nilai_rp', 'bulan');
        }

        // Ambil realisasi penjualan JUMLAH per bulan (sama tahun efektif)
        $penjualan = RtFactPenjualan::where('tahun', $tahunEfektif)
            ->where('sub_golongan_kode', 'JUMLAH')
            ->where('tipe', 'realisasi')
            ->where('is_akumulasi', 0)
            ->where('is_triwulan', 0)
            ->pluck('nilai_kwh', 'bulan');

        // Hitung HJR per bulan
        $calculated = [];
        for ($m = 1; $m <= 12; $m++) {
            $p = (float) ($pendapatan[$m] ?? 0);
            $j = (float) ($penjualan[$m] ?? 0);

            // Hindari division by zero
            $calculated[] = ($p > 0 && $j > 0)
                ? round($p / $j, 2)
                : null;
        }

        return [
            'calculated'    => $calculated,
            'tahun_efektif' => $tahunEfektif,
            'is_fallback'   => $tahunEfektif !== $tahun,
        ];
    }
}