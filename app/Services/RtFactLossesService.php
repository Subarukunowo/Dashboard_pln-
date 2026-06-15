<?php

namespace App\Services;

use App\Repositories\RtFactLossesRepository;
use App\Repositories\RtFactProduksiRepository;

class RtFactLossesService extends BaseService
{
    public function __construct(
        RtFactLossesRepository $repository,
        protected RtFactProduksiRepository $produksiRepo,
    ) {
        parent::__construct($repository);
    }

    public function getChartData(int $tahun, string $tipe = 'realisasi'): array
    {
        // Fallback ke tahun sebelumnya jika tidak ada data
        $hasData      = $this->repository->all(['tahun' => $tahun])->isNotEmpty();
        $tahunEfektif = $hasData ? $tahun : $tahun - 1;

        $produksi = $this->produksiRepo->all(['tahun' => $tahunEfektif])->keyBy('bulan');

        return [
            'tahun_efektif' => $tahunEfektif,
            'is_fallback'   => !$hasData,
            'withEmin'      => $this->buildSource($tahunEfektif, 'dengan_emin', $tipe, $produksi),
            'withoutEmin'   => $this->buildSource($tahunEfektif, 'tanpa_emin',  $tipe, $produksi),
        ];
    }

    protected function buildSource(int $tahun, string $tipeLosses, string $tipe, $produksi): array
    {
        $bulanan = $this->repository->all([
            'tahun'        => $tahun,
            'tipe_losses'  => $tipeLosses,
            'tipe'         => $tipe,
            'is_triwulan'  => 0,
            'is_akumulasi' => 0,
        ])->groupBy('bulan');

        $akumulasi = $this->repository->all([
            'tahun'        => $tahun,
            'tipe_losses'  => $tipeLosses,
            'tipe'         => $tipe,
            'is_triwulan'  => 0,
            'is_akumulasi' => 1,
        ])->groupBy('bulan');

        $target = $this->repository->all([
            'tahun'        => $tahun,
            'tipe_losses'  => $tipeLosses,
            'tipe'         => 'target',
            'is_triwulan'  => 0,
            'is_akumulasi' => 0,
        ])->groupBy('bulan');

        $result = [];

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $p = $produksi[$bulan] ?? null;
            $b = $bulanan[$bulan]  ?? collect();
            $a = $akumulasi[$bulan] ?? collect();
            $t = $target[$bulan]   ?? collect();

            $bTransmisi  = $b->firstWhere('kategori', 'transmisi');
            $bDistribusi = $b->firstWhere('kategori', 'distribusi');
            $bJaringan   = $b->firstWhere('kategori', 'jaringan');

            $aTransmisi  = $a->firstWhere('kategori', 'transmisi');
            $aDistribusi = $a->firstWhere('kategori', 'distribusi');
            $aJaringan   = $a->firstWhere('kategori', 'jaringan');

            $tJaringan = $t->firstWhere('kategori', 'jaringan');

            $result[] = [
                'bulan' => $bulan,

                // produksi
                'prodBruto'    => (float) ($p?->prod_bruto             ?? 0),
                'prodNetto'    => (float) ($p?->prod_netto             ?? 0),
                'siapSalur'    => (float) ($p?->siap_salur_transmisi   ?? 0),
                'interkoneksi' => (float) ($p?->interkoneksi           ?? 0),

                // losses transmisi
                'lossesKwh'     => (float) ($bTransmisi?->nilai_kwh  ?? 0),
                'lossesBulanan' => (float) ($bTransmisi?->nilai_pct  ?? 0) / 100,
                'lossesAkum'    => (float) ($aTransmisi?->nilai_pct  ?? 0) / 100,

                // losses distribusi
                'lossesDistribusiKwh'     => (float) ($bDistribusi?->nilai_kwh ?? 0),
                'lossesDistribusiBulanan' => (float) ($bDistribusi?->nilai_pct ?? 0) / 100,
                'lossesDistribusiAkum'    => (float) ($aDistribusi?->nilai_pct ?? 0) / 100,

                // losses jaringan
                'lossesJaringanKwh'     => (float) ($bJaringan?->nilai_kwh ?? 0),
                'lossesJaringanBulanan' => (float) ($bJaringan?->nilai_pct ?? 0) / 100,
                'lossesJaringanAkum'    => (float) ($aJaringan?->nilai_pct ?? 0) / 100,

                // target jaringan
                'lossesTarget' => (float) ($tJaringan?->nilai_pct ?? 0) / 100,
            ];
        }

        return $result;
    }
}