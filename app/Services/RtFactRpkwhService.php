<?php

namespace App\Services;

use App\Models\RtDimSubGolongan;
use App\Repositories\RtFactRpkwhRepository;
use Illuminate\Support\Facades\DB;

class MasterGolonganEmptyException extends \RuntimeException
{
}

class RtFactRpkwhService extends BaseService
{
    protected array $golonganList = ['S', 'R', 'B', 'I', 'P', 'M'];
    protected array $golonganJumlahKode = [
        'S' => 'JUMLAH-S', 'R' => 'JUMLAH-R', 'B' => 'JUMLAH-B',
        'I' => 'JUMLAH-I', 'P' => 'JUMLAH-P', 'M' => 'JUMLAH-M',
    ];

    public function __construct(RtFactRpkwhRepository $repository)
    {
        parent::__construct($repository);
    }

    public function getChartData(int $tahun, ?int $tahunLalu = null): array
    {
        $availableYears = DB::table('rt_fact_rpkwh')
            ->distinct()->orderByDesc('tahun')->pluck('tahun')->values()->all();

        $cek = $this->repository->all([
            'tahun' => $tahun, 'tipe' => 'realisasi', 'is_triwulan' => 0, 'is_akumulasi' => 0,
        ]);
        if ($cek->isEmpty()) {
            $tahun = $tahun - 1;
        }
        $tahunLalu = $tahunLalu ?? $tahun - 1;

        $rencanaRows = $this->repository->all([
            'tahun' => $tahun, 'tipe' => 'target', 'is_triwulan' => 0, 'is_akumulasi' => 0,
        ]);
        $realisasiRows = $this->repository->all([
            'tahun' => $tahun, 'tipe' => 'realisasi', 'is_triwulan' => 0, 'is_akumulasi' => 0,
        ]);

        $rencanaData   = $this->pivot($rencanaRows);
        $realisasiData = $this->pivot($realisasiRows);

        $tarif = array_unique(array_merge(array_keys($rencanaData), array_keys($realisasiData)));
        sort($tarif);
        foreach ($tarif as $t) {
            $rencanaData[$t]   = $rencanaData[$t]   ?? array_fill(0, 12, 0);
            $realisasiData[$t] = $realisasiData[$t] ?? array_fill(0, 12, 0);
        }

        // ── Data golongan tahun berjalan ─────────────────────────────────
        $targetGolBulRows = $this->repository->all([
            'tahun' => $tahun, 'tipe' => 'target', 'is_triwulan' => 0, 'is_akumulasi' => 0,
        ]);
        $realisasiGolBulRows = $this->repository->all([
            'tahun' => $tahun, 'tipe' => 'realisasi', 'is_triwulan' => 0, 'is_akumulasi' => 0,
        ]);

        $targetGolBul    = $this->pivotGolongan($targetGolBulRows);
        $realisasiGolBul = $this->pivotGolongan($realisasiGolBulRows);

        $golongan = array_map(fn ($g) => $this->golonganNama($g) . ' (' . $g . ')', $this->golonganList);

        foreach ($golongan as $key) {
            $targetGolBul[$key]    = $targetGolBul[$key]    ?? array_fill(0, 12, 0);
            $realisasiGolBul[$key] = $realisasiGolBul[$key] ?? array_fill(0, 12, 0);
        }

        $targetGolAkum    = [];
        $realisasiGolAkum = [];
        foreach ($golongan as $key) {
            $targetGolAkum[$key]    = $this->akumulasi($targetGolBul[$key]);
            $realisasiGolAkum[$key] = $this->akumulasi($realisasiGolBul[$key]);
        }

        // ── Data golongan tahun lalu (baseline pembanding growth YoY) ────
        $targetGolBulRowsLalu = $this->repository->all([
            'tahun' => $tahunLalu, 'tipe' => 'target', 'is_triwulan' => 0, 'is_akumulasi' => 0,
        ]);
        $realisasiGolBulRowsLalu = $this->repository->all([
            'tahun' => $tahunLalu, 'tipe' => 'realisasi', 'is_triwulan' => 0, 'is_akumulasi' => 0,
        ]);

        $targetGolBulLalu    = $this->pivotGolongan($targetGolBulRowsLalu);
        $realisasiGolBulLalu = $this->pivotGolongan($realisasiGolBulRowsLalu);

        foreach ($golongan as $key) {
            $targetGolBulLalu[$key]    = $targetGolBulLalu[$key]    ?? array_fill(0, 12, 0);
            $realisasiGolBulLalu[$key] = $realisasiGolBulLalu[$key] ?? array_fill(0, 12, 0);
        }

        $targetGolAkumLalu    = [];
        $realisasiGolAkumLalu = [];
        foreach ($golongan as $key) {
            $targetGolAkumLalu[$key]    = $this->akumulasi($targetGolBulLalu[$key]);
            $realisasiGolAkumLalu[$key] = $this->akumulasi($realisasiGolBulLalu[$key]);
        }

        // ── Growth per golongan (YoY): (realisasi ini - realisasi lalu) / realisasi lalu ──
        $growthBul  = [];
        $growthAkum = [];
        foreach ($golongan as $key) {
            $growthBul[$key]  = array_fill(0, 12, 0);
            $growthAkum[$key] = array_fill(0, 12, 0);

            for ($i = 0; $i < 12; $i++) {
                $ini  = $realisasiGolBul[$key][$i]     ?? 0;
                $lalu = $realisasiGolBulLalu[$key][$i]  ?? 0;
                $growthBul[$key][$i] = $lalu != 0 ? ($ini - $lalu) / $lalu : 0;

                $iniAkum  = $realisasiGolAkum[$key][$i]     ?? 0;
                $laluAkum = $realisasiGolAkumLalu[$key][$i] ?? 0;
                $growthAkum[$key][$i] = $laluAkum != 0 ? ($iniAkum - $laluAkum) / $laluAkum : 0;
            }
        }

        $bppData = [];
        $hjrData = [];

        $analisaData = $this->buildAnalisa($tahun, $tahunLalu);
        $persenData  = $this->buildPersen($tahun);

        return [
            'tarif'            => $tarif,
            'golongan'         => $golongan,
            'rencanaData'      => $rencanaData,
            'realisasiData'    => $realisasiData,
            'targetGolAkum'    => $targetGolAkum,
            'targetGolBul'     => $targetGolBul,
            'realisasiGolAkum' => $realisasiGolAkum,
            'realisasiGolBul'  => $realisasiGolBul,
            'growthAkum'       => $growthAkum,
            'growthBul'        => $growthBul,
            'bppData'          => $bppData,
            'hjrData'          => $hjrData,
            'analisaData'      => $analisaData,
            'persenData'       => $persenData,
            'tahun_efektif'    => $tahun,
            'tahun_lalu'       => $tahunLalu,
            'availableYears'   => $availableYears,
        ];
    }

    /**
     * Buat 12 baris data kosong (nilai_rpkwh = 0) untuk SEMUA sub-golongan
     * yang terdaftar di master rt_dim_sub_golongan. Dipakai tombol "Buat Data".
     *
     * @throws MasterGolonganEmptyException jika master golongan kosong
     */
public function buatTahun(int $tahun, string $tipe): int
    {
        $struktur = RtDimSubGolongan::select('kode', 'golongan_kode')->get();

        if ($struktur->isEmpty()) {
            throw new MasterGolonganEmptyException('Master golongan (rt_dim_sub_golongan) kosong');
        }

        $now  = now();    
        $rows = [];
        foreach ($struktur as $s) {
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $rows[] = [
                    'tahun'             => $tahun,
                    'bulan'             => $bulan,
                    'golongan_kode'     => $s->golongan_kode,
                    'sub_golongan_kode' => $s->kode,
                    'tipe'              => $tipe,
                    'is_triwulan'       => 0,
                    'is_akumulasi'      => 0,
                    'sumber'            => 'manual',
                    'nilai_rpkwh'       => 0,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ];
            }
        }

        DB::transaction(function () use ($rows) {
            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('rt_fact_rpkwh')->insert($chunk);
            }
        });

        return count($rows);
    }

    /**
     * Hapus data RPKWH untuk tahun & tipe tertentu. Dipakai tombol "Hapus Data".
     * $tipe: 'target' | 'realisasi' | 'semua'
     *
     * @return int jumlah baris yang terhapus
     */
    public function hapusData(int $tahun, string $tipe): int
    {
        $query = DB::table('rt_fact_rpkwh')->where('tahun', $tahun);

        if ($tipe !== 'semua') {
            $query->where('tipe', $tipe);
        }

        return $query->delete();
    }

    /** Cumulative sum 12 bulan — dipakai untuk semua akumulasi, ganti query is_akumulasi=1 */
    protected function akumulasi(array $bulanan): array
    {
        $result = [];
        $sum = 0;
        foreach ($bulanan as $i => $v) {
            $sum += (float) ($v ?? 0);
            $result[$i] = $sum;
        }
        return $result;
    }

    protected function pivot($rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $key = $row->sub_golongan_kode;
            $result[$key] ??= array_fill(0, 12, 0);
            $result[$key][$row->bulan - 1] = (float) $row->nilai_rpkwh;
        }
        return $result;
    }

    protected function pivotGolongan($rows): array
    {
        $result = [];
        $map = array_flip($this->golonganJumlahKode);
        foreach ($rows as $row) {
            $kode = $row->sub_golongan_kode;
            if (!isset($map[$kode])) {
                continue;
            }
            $g   = $map[$kode];
            $key = $this->golonganNama($g) . ' (' . $g . ')';
            $result[$key] ??= array_fill(0, 12, 0);
            $result[$key][$row->bulan - 1] = (float) $row->nilai_rpkwh;
        }
        return $result;
    }

    protected function golonganNama(string $kode): string
    {
        return match ($kode) {
            'S' => 'Sosial', 'R' => 'Rumah Tangga', 'B' => 'Bisnis',
            'I' => 'Industri', 'P' => 'Publik', 'M' => 'Multiguna',
            default => $kode,
        };
    }

    protected function buildAnalisa(int $tahunIni, int $tahunLalu): array
    {
        $getSeries = function (int $tahun, string $tipe) {
            $rows = $this->repository->all([
                'tahun' => $tahun, 'tipe' => $tipe, 'is_triwulan' => 0, 'is_akumulasi' => 0,
                'sub_golongan_kode' => 'JUMLAH',
            ]);
            $arr = array_fill(0, 12, 0);
            foreach ($rows as $row) {
                $arr[$row->bulan - 1] = (float) $row->nilai_rpkwh;
            }
            return $arr;
        };

        $targetIni  = $getSeries($tahunIni, 'target');
        $realIni    = $getSeries($tahunIni, 'realisasi');
        $targetLalu = $getSeries($tahunLalu, 'target');
        $realLalu   = $getSeries($tahunLalu, 'realisasi');

        $targetIniAkum  = $this->akumulasi($targetIni);
        $realIniAkum    = $this->akumulasi($realIni);
        $targetLaluAkum = $this->akumulasi($targetLalu);
        $realLaluAkum   = $this->akumulasi($realLalu);

        $bulanNama = [
            1=>'JANUARI',2=>'FEBRUARI',3=>'MARET',4=>'APRIL',5=>'MEI',6=>'JUNI',
            7=>'JULI',8=>'AGUSTUS',9=>'SEPTEMBER',10=>'OKTOBER',11=>'NOVEMBER',12=>'DESEMBER',
        ];

        $result = [];
        for ($i = 0; $i < 12; $i++) {
            $deltaRpkwh   = $realIni[$i] - $targetIni[$i];
            $deltaPct     = $targetIni[$i] != 0 ? $realIni[$i] / $targetIni[$i] : 0;
            $deltaAkum    = $realIniAkum[$i] - $targetIniAkum[$i];
            $deltaAkumPct = $targetIniAkum[$i] != 0 ? $realIniAkum[$i] / $targetIniAkum[$i] : 0;

            $result[] = [
                'bulan'               => $bulanNama[$i + 1],
                'targetLalu'          => $targetLalu[$i],
                'akumLalu'            => $targetLaluAkum[$i],
                'realisasiLalu'       => $realLalu[$i],
                'realisasiAkumLalu'   => $realLaluAkum[$i],
                'targetIni'           => $targetIni[$i],
                'akumIni'             => $targetIniAkum[$i],
                'realisasiIni'        => $realIni[$i],
                'realisasiAkumIni'    => $realIniAkum[$i],
                'deltaRpkwh'          => $deltaRpkwh,
                'deltaPct'            => $deltaPct,
                'deltaAkum'           => $deltaAkum,
                'deltaAkumPct'        => $deltaAkumPct,
                'pencapaianTarget'    => $targetLalu[$i] != 0 ? $targetIni[$i] / $targetLalu[$i] : 0,
                'pencapaianRealisasi' => $realLalu[$i] != 0 ? $realIni[$i] / $realLalu[$i] : 0,
            ];
        }

        return $result;
    }

    /**
     * Bulanan + akumulasi (cumulative sum) untuk target/realisasi tahun berjalan.
     * Dipakai chartPersenRpkwh — sekarang ikut merespons toggle Bulanan/Akumulasi.
     */
    protected function buildPersen(int $tahun): array
    {
        $rows = $this->repository->all([
            'tahun' => $tahun, 'is_triwulan' => 0, 'is_akumulasi' => 0,
            'sub_golongan_kode' => 'JUMLAH',
        ]);

        $bulanNama = [
            1=>'JANUARI',2=>'FEBRUARI',3=>'MARET',4=>'APRIL',5=>'MEI',6=>'JUNI',
            7=>'JULI',8=>'AGUSTUS',9=>'SEPTEMBER',10=>'OKTOBER',11=>'NOVEMBER',12=>'DESEMBER',
        ];

        $target = array_fill(0, 12, 0);
        $real   = array_fill(0, 12, 0);

        foreach ($rows as $row) {
            if ($row->tipe === 'target') {
                $target[$row->bulan - 1] = (float) $row->nilai_rpkwh;
            }
            if ($row->tipe === 'realisasi') {
                $real[$row->bulan - 1]   = (float) $row->nilai_rpkwh;
            }
        }

        $targetAkum = $this->akumulasi($target);
        $realAkum   = $this->akumulasi($real);

        $result = [];
        for ($i = 0; $i < 12; $i++) {
            $result[] = [
                'bulan'         => $bulanNama[$i + 1],
                'target'        => $target[$i],
                'realisasi'     => $real[$i],
                'delta'         => $real[$i] - $target[$i],
                'targetAkum'    => $targetAkum[$i],
                'realisasiAkum' => $realAkum[$i],
                'deltaAkum'     => $realAkum[$i] - $targetAkum[$i],
            ];
        }

        return $result;
    }
}