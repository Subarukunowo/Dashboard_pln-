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
        // Fallback ke tahun sebelumnya jika tidak ada data produksi sama sekali
        $hasData      = $this->produksiRepo->all(['tahun' => $tahun])->isNotEmpty();
        $tahunEfektif = $hasData ? $tahun : $tahun - 1;

        return [
            'tahun_efektif' => $tahunEfektif,
            'is_fallback'   => !$hasData,
            'withEmin'      => $this->buildSource($tahunEfektif, 'dengan_emin', $tipe),
            'withoutEmin'   => $this->buildSource($tahunEfektif, 'tanpa_emin', $tipe),
        ];
    }

    /**
     * Bangun data losses per bulan (1 tipe_losses) dengan MENGHITUNG langsung
     * dari data produksi mentah, bukan membaca nilai_kwh/nilai_pct dari
     * rt_fact_losses (yang sekarang hanya dipakai untuk simpan Target %).
     *
     * Rumus (sesuai sheet Excel "02.Losses ± EMIN", kolom L→N→U/V):
     *   Losses Transmisi kWh = loko_transmisi - siap_salur_transmisi - ps_gi
     *   Losses Transmisi %   = losses_kwh / loko_transmisi
     *
     *   Produksi Siap Salur  = prod_netto - losses_transmisi_kwh - ps_gi     (kolom L)
     *   Loko Distribusi      = Produksi Siap Salur - interkoneksi           (kolom N)
     *   Losses Distribusi kWh = loko_distribusi - ps_distribusi
     *                           - (blkg_padang+sembulang+buluh+p_nguan) - penjualan_tul3_09
     *   Losses Distribusi %   = losses_distribusi_kwh / loko_distribusi
     *   ⚠ ps_distribusi BUKAN pembagi — dia salah satu komponen yang
     *      dikurangkan, sama seperti blkg_padang/sembulang/dst.
     *
     *   Losses Jaringan kWh = prod_netto - ps_gi - interkoneksi - ps_distribusi
     *                         - (blkg_padang+sembulang+buluh+p_nguan) - penjualan_tul3_09
     *   Losses Jaringan %   = losses_kwh / prod_netto
     *
     *   Akumulasi % = SUM(losses_kwh s.d. bulan ini) / SUM(pembagi s.d. bulan ini)
     */
    protected function buildSource(int $tahun, string $tipeLosses, string $tipe): array
    {
        $produksi = $this->produksiRepo->all([
            'tahun'       => $tahun,
            'tipe_losses' => $tipeLosses,
        ])->keyBy('bulan');

        // Target % hanya disimpan manual di rt_fact_losses (kategori jaringan)
        $target = $this->repository->all([
            'tahun'        => $tahun,
            'tipe_losses'  => $tipeLosses,
            'tipe'         => 'target',
            'is_triwulan'  => 0,
            'is_akumulasi' => 0,
        ])->groupBy('bulan');

        $result = [];

        // akumulator untuk hitung persentase akumulasi (sum losses / sum pembagi)
        $akumLossesTrans = 0.0; $akumLokoTrans = 0.0;
        $akumLossesDist  = 0.0; $akumLokoDist  = 0.0;
        $akumLossesJar   = 0.0; $akumProdNetto = 0.0;

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $p = $produksi[$bulan] ?? null;

            $prodBruto  = (float) ($p?->prod_bruto ?? 0);
            $prodNetto  = (float) ($p?->prod_netto ?? 0);
            $lokoTrans  = (float) ($p?->loko_transmisi ?? 0);
            $siapSalur  = (float) ($p?->siap_salur_transmisi ?? 0);
            $psGi       = (float) ($p?->ps_gi ?? 0);
            $interkon   = (float) ($p?->interkoneksi ?? 0);
            $psDist     = (float) ($p?->ps_distribusi ?? 0);
            $kirimLain  = (float) ($p?->blkg_padang ?? 0)
                        + (float) ($p?->sembulang   ?? 0)
                        + (float) ($p?->buluh       ?? 0)
                        + (float) ($p?->p_nguan     ?? 0);
            $penjualan  = (float) ($p?->penjualan_tul3_09 ?? 0);

            // ── Losses Transmisi ──────────────────────────────────────────
            $lossesTransKwh = $lokoTrans - $siapSalur - $psGi;
            $lossesTransPct = $lokoTrans != 0 ? $lossesTransKwh / $lokoTrans : 0;

            // ── Losses Distribusi ─────────────────────────────────────────
            // Produksi Siap Salur Batam (kolom L) → Loko Distribusi (kolom N)
            $produksiSiapSalur = $prodNetto - $lossesTransKwh - $psGi;
            $lokoDist          = $produksiSiapSalur - $interkon;

            $lossesDistKwh = $lokoDist - $psDist - $kirimLain - $penjualan;
            $lossesDistPct = $lokoDist != 0 ? $lossesDistKwh / $lokoDist : 0;

            // ── Losses Jaringan ───────────────────────────────────────────
            $lossesJarKwh = $prodNetto - $psGi - $interkon - $psDist - $kirimLain - $penjualan;
            $lossesJarPct = $prodNetto != 0 ? $lossesJarKwh / $prodNetto : 0;

            // ── Akumulasi (sum kwh / sum pembagi s.d. bulan ini) ────────────
            $akumLossesTrans += $lossesTransKwh; $akumLokoTrans += $lokoTrans;
            $akumLossesDist  += $lossesDistKwh;  $akumLokoDist  += $lokoDist;
            $akumLossesJar   += $lossesJarKwh;   $akumProdNetto += $prodNetto;

            $lossesTransAkumPct = $akumLokoTrans != 0 ? $akumLossesTrans / $akumLokoTrans : 0;
            $lossesDistAkumPct  = $akumLokoDist  != 0 ? $akumLossesDist  / $akumLokoDist  : 0;
            $lossesJarAkumPct   = $akumProdNetto != 0 ? $akumLossesJar   / $akumProdNetto : 0;

            $tJaringan = ($target[$bulan] ?? collect())->firstWhere('kategori', 'jaringan');

            $result[] = [
                'bulan' => $bulan,

                // produksi (data primer, tetap editable)
                'prodBruto'    => $prodBruto,
                'prodNetto'    => $prodNetto,
                'lokoTransmisi'=> $lokoTrans,
                'siapSalur'    => $siapSalur,
                'psGi'         => $psGi,
                'interkoneksi' => $interkon,
                'psDistribusi' => $psDist,
                'kirimUnitLain'=> $kirimLain,
                'penjualan'    => $penjualan,

                // losses transmisi (dihitung)
                'lossesKwh'     => $lossesTransKwh,
                'lossesBulanan' => $lossesTransPct,
                'lossesAkum'    => $lossesTransAkumPct,

                // losses distribusi (dihitung)
                'lokoDistribusi'          => $lokoDist,
                'lossesDistribusiKwh'     => $lossesDistKwh,
                'lossesDistribusiBulanan' => $lossesDistPct,
                'lossesDistribusiAkum'    => $lossesDistAkumPct,

                // losses jaringan (dihitung)
                'lossesJaringanKwh'     => $lossesJarKwh,
                'lossesJaringanBulanan' => $lossesJarPct,
                'lossesJaringanAkum'    => $lossesJarAkumPct,

                // target jaringan (satu-satunya nilai manual)
                'lossesTarget' => (float) ($tJaringan?->nilai_pct ?? 0) / 100,
            ];
        }

        return $result;
    }
}