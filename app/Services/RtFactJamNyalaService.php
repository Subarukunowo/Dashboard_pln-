<?php

namespace App\Services;

use App\Models\RtDimSubGolongan;
use App\Repositories\RtFactJamNyalaRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RtFactJamNyalaService extends BaseService
{
    public function __construct(RtFactJamNyalaRepository $repository)
    {
        parent::__construct($repository);
    }

    public function getChartData(int $tahun, string $tipe = 'realisasi'): array
    {
        $rows = $this->repository->all([
            'tahun'        => $tahun,
            'tipe'         => $tipe,
            'is_triwulan'  => 0,
            'is_akumulasi' => 0,
        ]);

        $tahunEfektif = $tahun;
        if ($rows->isEmpty()) {
            $tahunEfektif = $tahun - 1;
            $rows = $this->repository->all([
                'tahun'        => $tahunEfektif,
                'tipe'         => $tipe,
                'is_triwulan'  => 0,
                'is_akumulasi' => 0,
            ]);
        }

        $rows = $rows->sortBy(['golongan_kode', 'bulan']);
        $tarif = [];
        $data  = [];

        foreach ($rows as $row) {
            $kode = $row->golongan_kode;
            if (!isset($data[$kode])) {
                $tarif[]     = $kode;
                $data[$kode] = array_fill(0, 12, null);
            }
            $data[$kode][$row->bulan - 1] = $row->nilai_jam !== null
                ? (float) $row->nilai_jam
                : null;
        }

        return compact('tarif', 'data') + ['tahun_efektif' => $tahunEfektif];
    }

    /**
     * Buat data 1 tahun penuh (12 bulan) untuk SEMUA kode yang ada di
     * rt_dim_sub_golongan, tanpa peduli sudah punya data atau belum.
     *
     * Sengaja TIDAK pakai insertOrIgnore(): method itu bisa diam-diam
     * gagal menyisipkan sebagian baris (constraint DB, kolom NOT NULL
     * tanpa default, tipe data mismatch, dll) tanpa exception apa pun.
     *
     * Di sini setiap baris di-create satu per satu, existing row dicek
     * lebih dulu (idempotent — aman dipanggil berkali-kali), dan setiap
     * kegagalan dicatat rinci ke log + dikembalikan ke response.
     */
    public function createTahun(int $tahun, string $tipe = 'target'): array
    {
        $kodeList = RtDimSubGolongan::pluck('kode');

        if ($kodeList->isEmpty()) {
            return [
                'created'       => 0,
                'skipped'       => 0,
                'failed'        => 0,
                'total_kode'    => 0,
                'tahun'         => $tahun,
                'tipe'          => $tipe,
                'failed_detail' => [],
            ];
        }

        $model = $this->repository->getModel();

        // Kombinasi (golongan_kode, bulan) yang SUDAH ada untuk tahun+tipe ini.
        $existingSet = $model
            ->where('tahun', $tahun)
            ->where('tipe', $tipe)
            ->where('is_triwulan', 0)
            ->where('is_akumulasi', 0)
            ->get(['golongan_kode', 'bulan'])
            ->map(fn ($r) => $r->golongan_kode . '|' . (int) $r->bulan)
            ->flip();

        $now          = now();
        $created      = 0;
        $skipped      = 0;
        $failed       = 0;
        $failedDetail = [];

        foreach ($kodeList as $kode) {
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $key = $kode . '|' . $bulan;

                if (isset($existingSet[$key])) {
                    $skipped++;
                    continue;
                }

                try {
                    DB::transaction(function () use ($model, $tahun, $bulan, $tipe, $kode, $now) {
                        $model->create([
                            'tahun'         => $tahun,
                            'bulan'         => $bulan,
                            'tipe'          => $tipe,
                            'golongan_kode' => $kode,
                            'nilai_jam'     => 0,
                            'is_triwulan'   => 0,
                            'is_akumulasi'  => 0,
                            'sumber'        => 'manual',
                            'created_at'    => $now,
                            'updated_at'    => $now,
                        ]);
                    });
                    $created++;
                } catch (\Throwable $e) {
                    $failed++;
                    $failedDetail[] = [
                        'golongan_kode' => $kode,
                        'bulan'         => $bulan,
                        'error'         => $e->getMessage(),
                    ];
                    Log::warning('[JamNyala] Gagal buat data', [
                        'tahun'         => $tahun,
                        'tipe'          => $tipe,
                        'golongan_kode' => $kode,
                        'bulan'         => $bulan,
                        'error'         => $e->getMessage(),
                    ]);
                }
            }
        }

        return [
            'created'       => $created,
            'skipped'       => $skipped,
            'failed'        => $failed,
            'total_kode'    => $kodeList->count(),
            'tahun'         => $tahun,
            'tipe'          => $tipe,
            'failed_detail' => $failedDetail,
        ];
    }

    /**
     * Rekap nilai jam nyala per golongan induk (S, R, B, I, P, M) + TOTAL,
     * dihitung dari baris individual supaya tidak double counting dengan
     * baris JUMLAH/JUMLAH-X yang sudah tersimpan.
     */
    public function getSummaryByGolongan(int $tahun, string $tipe = 'realisasi'): array
    {
        $labelMap = [
            'S' => 'Sosial', 'R' => 'Rumah Tangga', 'B' => 'Bisnis',
            'I' => 'Industri', 'P' => 'Publik', 'M' => 'Multiguna',
        ];

        $rows = $this->repository->all([
            'tahun'        => $tahun,
            'tipe'         => $tipe,
            'is_triwulan'  => 0,
            'is_akumulasi' => 0,
        ]);

        $golonganMap = RtDimSubGolongan::pluck('golongan_kode', 'kode');
        $summary     = [];
        $grandTotal  = array_fill(0, 12, 0.0);

        foreach ($rows as $row) {
            $kode = $row->golongan_kode;
            if (str_starts_with($kode, 'JUMLAH')) continue;

            $grup = $golonganMap[$kode] ?? null;
            if (!$grup || $grup === 'TOTAL') continue;

            if (!isset($summary[$grup])) {
                $summary[$grup] = array_fill(0, 12, 0.0);
            }

            $nilai = (float) ($row->nilai_jam ?? 0);
            $summary[$grup][$row->bulan - 1] += $nilai;
            $grandTotal[$row->bulan - 1]     += $nilai;
        }

        $result = [];
        foreach ($summary as $grup => $perBulan) {
            $result[$grup] = [
                'golongan'  => $grup,
                'label'     => $labelMap[$grup] ?? $grup,
                'per_bulan' => $perBulan,
                'total'     => array_sum($perBulan),
            ];
        }

        $result['TOTAL'] = [
            'golongan'  => 'TOTAL',
            'label'     => 'Jumlah Total',
            'per_bulan' => $grandTotal,
            'total'     => array_sum($grandTotal),
        ];

        return $result;
    }

    /**
     * Daftar tahun yang benar-benar punya data — untuk select tahun
     * di modal Muat dan Hapus.
     */
    public function getAvailableYears(): array
    {
        return $this->repository->getModel()
            ->select('tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->map(fn ($t) => (int) $t)
            ->values()
            ->all();
    }

    /**
     * Detail Target + Realisasi (12 bulan) untuk SATU golongan_kode.
     * Kalau baris belum ada di DB, tetap dikembalikan null (bukan di-skip)
     * supaya golongan tersebut tetap bisa diedit/dibuat dari panel edit.
     */
    public function getDetailByGolongan(int $tahun, string $golonganKode): array
    {
        $rows = $this->repository->getModel()
            ->where('tahun', $tahun)
            ->where('golongan_kode', $golonganKode)
            ->where('is_triwulan', 0)
            ->where('is_akumulasi', 0)
            ->get();

        $target    = array_fill(0, 12, null);
        $realisasi = array_fill(0, 12, null);
        $ids       = array_fill(0, 12, ['target' => null, 'realisasi' => null]);

        foreach ($rows as $row) {
            $idx = $row->bulan - 1;
            if ($idx < 0 || $idx > 11) continue;

            if ($row->tipe === 'target') {
                $target[$idx]        = $row->nilai_jam !== null ? (float) $row->nilai_jam : null;
                $ids[$idx]['target'] = $row->id;
            } elseif ($row->tipe === 'realisasi') {
                $realisasi[$idx]        = $row->nilai_jam !== null ? (float) $row->nilai_jam : null;
                $ids[$idx]['realisasi'] = $row->id;
            }
        }

        return compact('target', 'realisasi', 'ids') + [
            'tahun'         => $tahun,
            'golongan_kode' => $golonganKode,
        ];
    }
}