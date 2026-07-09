<?php

namespace App\Services;

use App\Models\RtDimSubGolongan;
use App\Repositories\RtFactPenjualanRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RtFactPenjualanService extends BaseService
{
    public function __construct(RtFactPenjualanRepository $repository)
    {
        parent::__construct($repository);
    }

    public function getChartData(int $tahun): array
    {
        // Fallback ke tahun sebelumnya jika kosong
        $hasData      = $this->repository->all(['tahun' => $tahun, 'is_triwulan' => 0])->isNotEmpty();
        $tahunEfektif = $hasData ? $tahun : $tahun - 1;

        $allData = $this->repository->all(['tahun' => $tahunEfektif, 'is_triwulan' => 0]);

        $bulananTarget    = $allData->where('is_akumulasi', 0)->where('tipe', 'target')->groupBy('sub_golongan_kode');
        $bulananRealisasi = $allData->where('is_akumulasi', 0)->where('tipe', 'realisasi')->groupBy('sub_golongan_kode');
        $akumTarget       = $allData->where('is_akumulasi', 1)->where('tipe', 'target')->groupBy('sub_golongan_kode');
        $akumRealisasi    = $allData->where('is_akumulasi', 1)->where('tipe', 'realisasi')->groupBy('sub_golongan_kode');

        $allKodes = $bulananTarget->keys()->merge($bulananRealisasi->keys())->unique()->values();

        $perTarif = [];
        foreach ($allKodes as $kode) {
            $t  = $this->toMonthly($bulananTarget[$kode]    ?? collect(), 'nilai_kwh');
            $r  = $this->toMonthly($bulananRealisasi[$kode] ?? collect(), 'nilai_kwh');
            $ta = $this->toMonthly($akumTarget[$kode]       ?? collect(), 'nilai_kwh');
            $ra = $this->toMonthly($akumRealisasi[$kode]    ?? collect(), 'nilai_kwh');

            $perTarif[$kode] = [
                'target'        => $t,
                'realisasi'     => $r,
                'targetAkum'    => $ta,
                'realisasiAkum' => $ra,
            ];
        }

        // JUMLAH untuk view utama
        $jumlah = $perTarif['JUMLAH'] ?? [
            'target'        => array_fill(0, 12, 0),
            'realisasi'     => array_fill(0, 12, 0),
            'targetAkum'    => array_fill(0, 12, 0),
            'realisasiAkum' => array_fill(0, 12, 0),
        ];

        // % pencapaian bulanan
        $persenBulanan = array_map(
            fn($r, $t) => $t > 0 ? round($r / $t * 100, 2) : null,
            $jumlah['realisasi'], $jumlah['target']
        );

        // % pencapaian akumulasi
        $persenAkumulasi = array_map(
            fn($r, $t) => $t > 0 ? round($r / $t * 100, 2) : null,
            $jumlah['realisasiAkum'], $jumlah['targetAkum']
        );

        return [
            'tahun_efektif'   => $tahunEfektif,
            'is_fallback'     => !$hasData,
            'bulanan'         => $jumlah,
            'persenBulanan'   => $persenBulanan,
            'akumulasi'       => ['target' => $jumlah['targetAkum'], 'realisasi' => $jumlah['realisasiAkum']],
            'persenAkumulasi' => $persenAkumulasi,
            'perTarif'        => $perTarif,
            'tarifList'       => $allKodes->values()->all(),
        ];
    }

    /**
     * Backend adalah source of truth untuk golongan_kode.
     * Nilai golongan_kode yang dikirim frontend akan DIABAIKAN dan
     * dihitung ulang dari sub_golongan_kode via rt_dim_sub_golongan.
     * Pola identik modul pelanggan; data existing sudah pakai format
     * JUMLAH-S/R/B/I/P/M sehingga langsung cocok dengan dim table.
     */
    public function resolveGolonganKode(?string $subGolonganKode): ?string
    {
        if (!$subGolonganKode) {
            return null;
        }

        static $map = null;
        if ($map === null) {
            $map = RtDimSubGolongan::pluck('golongan_kode', 'kode')->all();
        }

        return $map[$subGolonganKode] ?? null;
    }

    public function create(array $data): Model
    {
        if (!empty($data['sub_golongan_kode'])) {
            $resolved = $this->resolveGolonganKode($data['sub_golongan_kode']);
            if ($resolved !== null) {
                $data['golongan_kode'] = $resolved;
            }
        }

        return parent::create($data);
    }

    public function update(int|string $id, array $data): Model
    {
        if (!empty($data['sub_golongan_kode'])) {
            $resolved = $this->resolveGolonganKode($data['sub_golongan_kode']);
            if ($resolved !== null) {
                $data['golongan_kode'] = $resolved;
            }
        }

        return parent::update($id, $data);
    }

    /**
     * Generate 12 baris/bulan (nilai=0) untuk satu tahun+tipe, berdasarkan
     * SEMUA baris rt_dim_sub_golongan — mencakup tarif asli DAN JUMLAH-S/R/B/I/P/M
     * DAN baris JUMLAH/TOTAL grand-total. Pola identik modul pelanggan.
     */
    public function buatTahun(int $tahun, string $tipe): array
    {
        return DB::transaction(function () use ($tahun, $tipe) {
            $now  = now();
            $rows = [];

            $dims = RtDimSubGolongan::select('kode', 'golongan_kode')->get();

            foreach ($dims as $dim) {
                $isAkumulasi = str_starts_with($dim->kode, 'JUMLAH') ? 1 : 0;

                for ($bulan = 1; $bulan <= 12; $bulan++) {
                    $rows[] = [
                        'tahun'             => $tahun,
                        'bulan'             => $bulan,
                        'tipe'              => $tipe,
                        'golongan_kode'     => $dim->golongan_kode,
                        'sub_golongan_kode' => $dim->kode,
                        'nilai_kwh'         => 0,
                        'is_triwulan'       => 0,
                        'is_akumulasi'      => $isAkumulasi,
                        'sumber'            => 'manual',
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ];
                }
            }

            DB::table('rt_fact_penjualan')->insertOrIgnore($rows);

            return ['tahun' => $tahun, 'tipe' => $tipe, 'rows_created' => count($rows)];
        });
    }

    private function toMonthly($rows, string $field): array
    {
        $result = array_fill(0, 12, null);
        foreach ($rows as $row) {
            $result[$row->bulan - 1] = (float) $row->$field;
        }
        return $result;
    }
}