<?php

namespace App\Services;

use App\Repositories\RtFactPendapatanRepository;

class RtFactPendapatanService extends BaseService
{
    public function __construct(RtFactPendapatanRepository $repository)
    {
        parent::__construct($repository);
    }

    public function getChartData(int $tahun): array
    {
        // Fallback ke tahun sebelumnya jika kosong
        $hasData      = $this->repository->all(['tahun' => $tahun, 'is_triwulan' => 0])->isNotEmpty();
        $tahunEfektif = $hasData ? $tahun : $tahun - 1;

        $allData = $this->repository->all(['tahun' => $tahunEfektif, 'is_triwulan' => 0]);

        // ── Pivot per sub_golongan_kode ────────────────────────────────────
        $tarif   = [];
        $data    = [];

        $bulananTarget    = $allData->where('is_akumulasi', 0)->where('tipe', 'target')->groupBy('sub_golongan_kode');
        $bulananRealisasi = $allData->where('is_akumulasi', 0)->where('tipe', 'realisasi')->groupBy('sub_golongan_kode');
        $akumTarget       = $allData->where('is_akumulasi', 1)->where('tipe', 'target')->groupBy('sub_golongan_kode');
        $akumRealisasi    = $allData->where('is_akumulasi', 1)->where('tipe', 'realisasi')->groupBy('sub_golongan_kode');

        $allKodes = $bulananTarget->keys()->merge($bulananRealisasi->keys())->unique()->values();

        foreach ($allKodes as $kode) {
            $tarif[] = $kode;

            $data[$kode] = [
                'rencana'       => $this->toMonthly($bulananTarget[$kode]    ?? collect()),
                'realisasi'     => $this->toMonthly($bulananRealisasi[$kode] ?? collect()),
                'rencanaAkum'   => $this->toMonthly($akumTarget[$kode]       ?? collect()),
                'realisasiAkum' => $this->toMonthly($akumRealisasi[$kode]    ?? collect()),
            ];
        }

        // ── Realisasi bulanan total (JUMLAH) ───────────────────────────────
        $realisasiBulanan = [];
        for ($m = 1; $m <= 12; $m++) {
            $tRow = $allData->where('is_akumulasi', 0)->where('tipe', 'target')
                ->where('sub_golongan_kode', 'JUMLAH')->where('bulan', $m)->first();
            $rRow = $allData->where('is_akumulasi', 0)->where('tipe', 'realisasi')
                ->where('sub_golongan_kode', 'JUMLAH')->where('bulan', $m)->first();

            $t = (float) ($tRow?->nilai_rp ?? 0);
            $r = (float) ($rRow?->nilai_rp ?? 0);

            $realisasiBulanan[] = [
                'bulan'      => $m,
                'target'     => $t,
                'realisasi'  => $r,
                'delta'      => $r - $t,
                'pencapaian' => $t > 0 ? $r / $t : 0,
            ];
        }

        // ── Data per golongan besar untuk GolTarif ────────────────────────
        $golList = ['S', 'R', 'B', 'I', 'P', 'M'];
        $golonganData = [];

        foreach ($golList as $gol) {
            $rows = $allData->where('is_akumulasi', 0)->where('golongan_kode', $gol);
            $jumlahKode = 'JUMLAH-' . $gol;

            $golonganData[$gol] = [
                'target'    => $this->toMonthly($rows->where('tipe', 'target')->where('sub_golongan_kode', $jumlahKode)),
                'realisasi' => $this->toMonthly($rows->where('tipe', 'realisasi')->where('sub_golongan_kode', $jumlahKode)),
            ];
        }

        return [
            'tahun_efektif'    => $tahunEfektif,
            'is_fallback'      => !$hasData,
            'tarif'            => $tarif,
            'data'             => $data,
            'realisasiBulanan' => $realisasiBulanan,
            'golongan'         => $golonganData,
        ];
    }

    private function toMonthly($rows): array
    {
        $result = array_fill(0, 12, null);
        foreach ($rows as $row) {
            $result[$row->bulan - 1] = (float) $row->nilai_rp;
        }
        return $result;
    }
}