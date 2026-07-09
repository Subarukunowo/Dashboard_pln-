<?php

namespace App\Services;

use App\Repositories\RtFactTunggakanRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RtFactTunggakanService extends BaseService
{
    // Default awal saat createTahun; setelahnya nilai target diambil
    // dari row tipe='target' di DB (bisa diedit user per bulan).
    const DEFAULT_RATIO_TARGET_PCT = 0.01; // 0,01%

    public function __construct(RtFactTunggakanRepository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Gabungkan breakdown (pal_berjalan/pal_tunggakan, ts_berjalan/ts_tunggakan)
     * menjadi total pal & tagihan_susulan. Kolom total ini dipertahankan
     * supaya kode/laporan lain yang masih membaca `pal` & `tagihan_susulan`
     * tetap jalan tanpa perubahan.
     */
    private function hitungTotalPalTs(array $data): array
    {
        if (array_key_exists('pal_berjalan', $data) || array_key_exists('pal_tunggakan', $data)) {
            $palBerjalan  = (float) ($data['pal_berjalan'] ?? 0);
            $palTunggakan = (float) ($data['pal_tunggakan'] ?? 0);
            $data['pal']  = $palBerjalan + $palTunggakan;
        }

        if (array_key_exists('ts_berjalan', $data) || array_key_exists('ts_tunggakan', $data)) {
            $tsBerjalan  = (float) ($data['ts_berjalan'] ?? 0);
            $tsTunggakan = (float) ($data['ts_tunggakan'] ?? 0);
            $data['tagihan_susulan'] = $tsBerjalan + $tsTunggakan;
        }

        return $data;
    }

    /**
     * Hitung ratio_pct otomatis untuk row tipe='realisasi'.
     * Rumus (dari Excel, sheet KINERJA TUNGGAKAN, kolom I = G/H):
     * ratio = jumlah_tunggakan / pendapatan * 100
     * Row tipe='target' TIDAK dihitung otomatis — ratio_pct adalah
     * nilai target itu sendiri (input manual, default 0.01%).
     */
    private function hitungRatio(array $data): array
    {
        $tipe = $data['tipe'] ?? 'realisasi';

        if ($tipe === 'realisasi') {
            $jumlahTunggakan = (float) ($data['jumlah_tunggakan'] ?? 0);
            $pendapatan      = (float) ($data['pendapatan'] ?? 0);

            $data['ratio_pct'] = $pendapatan > 0
                ? round(($jumlahTunggakan / $pendapatan) * 100, 6)
                : 0;
        }

        return $data;
    }

    public function create(array $data): Model
    {
        $data = $this->hitungTotalPalTs($data);
        $data = $this->hitungRatio($data);

        return parent::create($data);
    }

    public function update(int|string $id, array $data): Model
    {
        $existing = $this->repository->findById($id);

        $merged = array_merge([
            'tipe'             => $existing->tipe,
            'pal_berjalan'     => $existing->pal_berjalan ?? 0,
            'pal_tunggakan'    => $existing->pal_tunggakan ?? 0,
            'ts_berjalan'      => $existing->ts_berjalan ?? 0,
            'ts_tunggakan'     => $existing->ts_tunggakan ?? 0,
            'jumlah_tunggakan' => $existing->jumlah_tunggakan ?? 0,
            'pendapatan'       => $existing->pendapatan ?? 0,
            'ratio_pct'        => $existing->ratio_pct ?? 0,
        ], $data);

        $merged = $this->hitungTotalPalTs($merged);
        $merged = $this->hitungRatio($merged);

        // Jika salah satu field breakdown PAL dikirim, simpan seluruh breakdown + totalnya
        if (array_key_exists('pal_berjalan', $data) || array_key_exists('pal_tunggakan', $data)) {
            $data['pal_berjalan']  = $merged['pal_berjalan'];
            $data['pal_tunggakan'] = $merged['pal_tunggakan'];
            $data['pal']           = $merged['pal'];
        }

        // Jika salah satu field breakdown TS dikirim, simpan seluruh breakdown + totalnya
        if (array_key_exists('ts_berjalan', $data) || array_key_exists('ts_tunggakan', $data)) {
            $data['ts_berjalan']     = $merged['ts_berjalan'];
            $data['ts_tunggakan']    = $merged['ts_tunggakan'];
            $data['tagihan_susulan'] = $merged['tagihan_susulan'];
        }

        if (($merged['tipe'] ?? 'realisasi') === 'realisasi') {
            $data['ratio_pct'] = $merged['ratio_pct'];
        }

        return parent::update($id, $data);
    }

    /**
     * Buat data tunggakan tahun baru: 12 bulan x 2 tipe (realisasi & target).
     * Tidak ada breakdown per golongan — tabel rt_fact_tunggakan tidak
     * punya kolom golongan_kode.
     */
    public function createTahun(int $tahun): void
    {
        DB::transaction(function () use ($tahun) {
            foreach (range(1, 12) as $bulan) {
                foreach (['realisasi', 'target'] as $tipe) {
                    $exists = $this->repository->all([
                        'tahun' => $tahun,
                        'bulan' => $bulan,
                        'tipe'  => $tipe,
                    ])->isNotEmpty();

                    if ($exists) {
                        continue;
                    }

                    $payload = [
                        'tahun'            => $tahun,
                        'bulan'            => $bulan,
                        'tipe'             => $tipe,
                        'pal'              => 0,
                        'pal_berjalan'     => 0,
                        'pal_tunggakan'    => 0,
                        'tagihan_susulan'  => 0,
                        'ts_berjalan'      => 0,
                        'ts_tunggakan'     => 0,
                        'jumlah_tunggakan' => 0,
                        'pendapatan'       => 0,
                        'sumber'           => 'manual',
                    ];

                    if ($tipe === 'target') {
                        $payload['ratio_pct'] = self::DEFAULT_RATIO_TARGET_PCT;
                    }

                    $this->create($payload);
                }
            }
        });
    }

    public function getChartData(int $tahun): array
    {
        $rows = $this->repository->all(['tahun' => $tahun])->sortBy('bulan');

        $tahunEfektif = $tahun;
        $isFallback   = false;

        if ($rows->isEmpty()) {
            $tahunEfektif = $tahun - 1;
            $isFallback   = true;
            $rows = $this->repository->all(['tahun' => $tahunEfektif])->sortBy('bulan');
        }

        $bulanNama = [
            1=>'JANUARI',2=>'FEBRUARI',3=>'MARET',4=>'APRIL',5=>'MEI',6=>'JUNI',
            7=>'JULI',8=>'AGUSTUS',9=>'SEPTEMBER',10=>'OKTOBER',11=>'NOVEMBER',12=>'DESEMBER',
        ];

        $grouped = $rows->groupBy('bulan')->map(function ($rowsBulan, $bulan) use ($bulanNama) {
            $realisasi = $rowsBulan->firstWhere('tipe', 'realisasi');
            $target    = $rowsBulan->firstWhere('tipe', 'target');

            return [
                'idRealisasi'     => $realisasi->id ?? null,
                'idTarget'        => $target->id ?? null,
                'bulan'           => $bulanNama[$bulan] ?? $bulan,
                'pal'             => (float) ($realisasi->pal ?? 0),
                'palBerjalan'     => (float) ($realisasi->pal_berjalan ?? 0),
                'palTunggakan'    => (float) ($realisasi->pal_tunggakan ?? 0),
                'tagihanSusulan'  => (float) ($realisasi->tagihan_susulan ?? 0),
                'tsBerjalan'      => (float) ($realisasi->ts_berjalan ?? 0),
                'tsTunggakan'     => (float) ($realisasi->ts_tunggakan ?? 0),
                'jumlahTunggakan' => (float) ($realisasi->jumlah_tunggakan ?? 0),
                'pendapatan'      => (float) ($realisasi->pendapatan ?? 0),
                'ratioTunggakan'  => (float) (($realisasi->ratio_pct ?? 0) / 100),
                'ratioTarget'     => (float) (($target->ratio_pct ?? self::DEFAULT_RATIO_TARGET_PCT) / 100),
            ];
        })->sortKeys()->values()->all();

        return [
            'data'          => $grouped,
            'tahun_efektif' => $tahunEfektif,
            'is_fallback'   => $isFallback,
        ];
    }
}