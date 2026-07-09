<?php

namespace App\Services;

use App\Repositories\RtFactPendapatanRepository;
use Illuminate\Support\Facades\DB;

class RtFactPendapatanService extends BaseService
{
    public function __construct(RtFactPendapatanRepository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * $tahun = tahun yang dipilih user di filter (state.tahun).
     * Fungsi ini SELALU mengambil $tahun dan $tahun-1 sekaligus dari database,
     * tanpa asumsi "tahun berjalan" — murni berdasarkan apa yang tersedia di data.
     */
    public function getChartData(int $tahun): array
    {
        $tahunLalu = $tahun - 1;

        // Ambil kedua tahun sekaligus dalam satu query.
        // Catatan: is_akumulasi tidak lagi difilter di sini karena akumulasi
        // sekarang selalu dihitung (cumsum) dari data bulanan, bukan diquery manual.
        $allData = $this->repository->all([
            'tahun'       => [$tahun, $tahunLalu],
            'is_triwulan' => 0,
            'is_akumulasi' => 0,
        ]);

        // Fallback: kalau tahun yang diminta benar-benar kosong di kedua tahun, jangan diam-diam
        // ganti tahun (itu sumber bug sebelumnya) — cukup laporkan lewat flag ke frontend.
        $hasIni  = $allData->where('tahun', $tahun)->isNotEmpty();
        $hasLalu = $allData->where('tahun', $tahunLalu)->isNotEmpty();

        $dimSubs = DB::table('rt_dim_sub_golongan')
            ->orderByRaw("FIELD(golongan_kode,'S','R','B','I','P','M','TOTAL')")
            ->orderBy('urutan')
            ->get()
            ->keyBy('kode');

        // ---------- Section: daftar sub-golongan (dipakai modul Pendapatan Batam / detail) ----------
        $bulanan = fn ($tahunX, $tipe) => $allData
            ->where('tahun', $tahunX)->where('tipe', $tipe)
            ->groupBy('sub_golongan_kode');

        $bulTargetIni    = $bulanan($tahun, 'target');
        $bulRealisasiIni = $bulanan($tahun, 'realisasi');

        $allKodes = $dimSubs->keys()
            ->merge($bulTargetIni->keys()->merge($bulRealisasiIni->keys())->unique()->values())
            ->unique()->values();

        $tarif = [];
        $data  = [];

        foreach ($allKodes as $kode) {
            $tarif[] = $kode;

            $rencanaMonthly   = $this->toMonthly($bulTargetIni[$kode]    ?? collect());
            $realisasiMonthly = $this->toMonthly($bulRealisasiIni[$kode] ?? collect());

            $data[$kode] = [
                'nama'          => $dimSubs[$kode]->nama          ?? $kode,
                'golongan_kode' => $dimSubs[$kode]->golongan_kode ?? 'TOTAL',
                'rencana'       => $rencanaMonthly,
                'realisasi'     => $realisasiMonthly,
                'rencanaAkum'   => $this->toCumulative($rencanaMonthly),
                'realisasiAkum' => $this->toCumulative($realisasiMonthly),
            ];
        }

        // ---------- Section 2: Realisasi bulanan TOTAL (JUMLAH) — tahun yang dipilih ----------
        $realisasiBulanan = [];
        for ($m = 1; $m <= 12; $m++) {
            $tRow = $allData->where('tahun', $tahun)
                ->where('tipe', 'target')->where('sub_golongan_kode', 'JUMLAH')->where('bulan', $m)->first();
            $rRow = $allData->where('tahun', $tahun)
                ->where('tipe', 'realisasi')->where('sub_golongan_kode', 'JUMLAH')->where('bulan', $m)->first();

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

        // ---------- Section 1: Pie per golongan besar (S,R,B,I,P,M) ----------
        // Dibuat lengkap 4 series per golongan: target/realisasi × bulanan/akumulasi,
        // untuk tahun ini DAN tahun lalu, supaya kombinasi filter (tahun × view) apa pun tetap valid.
        // Akumulasi = cumsum dari series bulanan, tidak lagi diquery dari is_akumulasi=1.
        $golList  = ['S', 'R', 'B', 'I', 'P', 'M'];
        $golLabel = [
            'S' => 'Sosial', 'R' => 'Rumah Tangga', 'B' => 'Bisnis',
            'I' => 'Industri', 'P' => 'Publik', 'M' => 'Multiguna',
        ];
        $golonganData = [];

        foreach ($golList as $gol) {
            $jumlahKode = 'JUMLAH-' . $gol;

            $seriesFor = function (int $tahunX, string $tipe) use ($allData, $gol, $jumlahKode) {
                $rows = $allData->where('tahun', $tahunX)
                    ->where('golongan_kode', $gol)->where('tipe', $tipe)
                    ->where('sub_golongan_kode', $jumlahKode);
                return $this->toMonthly($rows);
            };

            $targetIniBul      = $seriesFor($tahun,     'target');
            $realisasiIniBul   = $seriesFor($tahun,     'realisasi');
            $targetLaluBul     = $seriesFor($tahunLalu, 'target');
            $realisasiLaluBul  = $seriesFor($tahunLalu, 'realisasi');

            $golonganData[] = [
                'kode'               => $gol,
                'nama'               => $golLabel[$gol],
                'targetIniBul'       => $targetIniBul,
                'targetIniAkum'      => $this->toCumulative($targetIniBul),
                'realisasiIniBul'    => $realisasiIniBul,
                'realisasiIniAkum'   => $this->toCumulative($realisasiIniBul),
                'targetLaluBul'      => $targetLaluBul,
                'targetLaluAkum'     => $this->toCumulative($targetLaluBul),
                'realisasiLaluBul'   => $realisasiLaluBul,
                'realisasiLaluAkum'  => $this->toCumulative($realisasiLaluBul),
            ];
        }

        // ---------- Section 3: Total TOTAL/JUMLAH — tahun lalu vs tahun ini, per bulan ----------
        $total = [];
        for ($m = 1; $m <= 12; $m++) {
            $find = fn ($tahunX, $tipe) => $allData->where('tahun', $tahunX)
                ->where('tipe', $tipe)->where('sub_golongan_kode', 'JUMLAH')->where('bulan', $m)->first();

            $targetLalu    = (float) ($find($tahunLalu, 'target')?->nilai_rp    ?? 0);
            $realisasiLalu = (float) ($find($tahunLalu, 'realisasi')?->nilai_rp ?? 0);
            $targetIni     = (float) ($find($tahun,     'target')?->nilai_rp    ?? 0);
            $realisasiIni  = (float) ($find($tahun,     'realisasi')?->nilai_rp ?? 0);

            $total[] = [
                'bulan'         => $m,
                'targetLalu'    => $targetLalu,
                'realisasiLalu' => $realisasiLalu,
                'targetIni'     => $targetIni,
                'realisasiIni'  => $realisasiIni,
                'deltaKwh'      => $realisasiIni - $targetIni,
                'deltaPct'      => $targetIni > 0 ? $realisasiIni / $targetIni : 1,
            ];
        }

        return [
            'tahun'            => $tahun,
            'tahunLalu'        => $tahunLalu,
            'hasDataTahunIni'  => $hasIni,
            'hasDataTahunLalu' => $hasLalu,
            'tarif'            => $tarif,
            'data'             => $data,
            'realisasiBulanan' => $realisasiBulanan,
            'golongan'         => $golonganData,
            'total'            => $total,
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

    /**
     * Ubah array 12 nilai bulanan (boleh null) menjadi running total (cumsum),
     * setara rumus Excel semacam D7 = D6 + C7.
     * Bulan yang null dilewati (tidak menambah 0) dan tetap null di hasil,
     * supaya bulan yang belum ada datanya tidak seolah-olah sudah "0 tercapai".
     */
    private function toCumulative(array $monthly): array
    {
        $result = [];
        $running = 0.0;
        $adaData = false;

        foreach ($monthly as $val) {
            if ($val !== null) {
                $running += $val;
                $adaData = true;
            }
            $result[] = $adaData ? round($running, 2) : null;
        }

        return $result;
    }
}