<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RtDatabaseStatsController extends BaseApiController
{
    /**
     * Daftar semua tabel RT beserta label dan metadata-nya.
     */
    private function tableMap(): array
    {
        return [
            // Dimensi
            [
                'table'    => 'rt_dim_sub_golongan',
                'label'    => 'Dim Sub Golongan Tarif',
                'icon'     => 'tag',
                'kategori' => 'Dimensi',
                'pk'       => 'kode',
                'has_tahun'=> false,
            ],

            // Produksi & Biaya
            [
                'table'    => 'rt_fact_bpp',
                'label'    => 'BPP (Biaya Pokok Produksi)',
                'icon'     => 'layers',
                'kategori' => 'Produksi & Biaya',
                'pk'       => 'id',
                'has_tahun'=> true,
            ],
            [
                'table'    => 'rt_fact_produksi',
                'label'    => 'Produksi',
                'icon'     => 'zap',
                'kategori' => 'Produksi & Biaya',
                'pk'       => 'id',
                'has_tahun'=> true,
            ],
            [
                'table'    => 'rt_fact_losses',
                'label'    => 'Losses & EMIN',
                'icon'     => 'trending-down',
                'kategori' => 'Produksi & Biaya',
                'pk'       => 'id',
                'has_tahun'=> true,
            ],

            // Penjualan
            [
                'table'    => 'rt_fact_penjualan',
                'label'    => 'Kinerja Penjualan (KWH)',
                'icon'     => 'activity',
                'kategori' => 'Penjualan',
                'pk'       => 'id',
                'has_tahun'=> true,
            ],
            [
                'table'    => 'rt_fact_babin',
                'label'    => 'Babin (Beli Antar Unit)',
                'icon'     => 'globe',
                'kategori' => 'Penjualan',
                'pk'       => 'id',
                'has_tahun'=> true,
            ],
            [
                'table'    => 'rt_fact_jam_nyala',
                'label'    => 'Jam Nyala',
                'icon'     => 'clock',
                'kategori' => 'Penjualan',
                'pk'       => 'id',
                'has_tahun'=> true,
            ],

            // Pendapatan
            [
                'table'    => 'rt_fact_pendapatan',
                'label'    => 'Pendapatan (Rp)',
                'icon'     => 'dollar-sign',
                'kategori' => 'Pendapatan',
                'pk'       => 'id',
                'has_tahun'=> true,
            ],
            [
                'table'    => 'rt_fact_rpkwh',
                'label'    => 'RP/KWH',
                'icon'     => 'bar-chart-2',
                'kategori' => 'Pendapatan',
                'pk'       => 'id',
                'has_tahun'=> true,
            ],
            [
                'table'    => 'rt_fact_hjr',
                'label'    => 'HJR (Harga Jual Rata-rata)',
                'icon'     => 'percent',
                'kategori' => 'Pendapatan',
                'pk'       => 'id',
                'has_tahun'=> true,
            ],

            // Pelanggan
            [
                'table'    => 'rt_fact_pelanggan',
                'label'    => 'Pelanggan',
                'icon'     => 'users',
                'kategori' => 'Pelanggan',
                'pk'       => 'id',
                'has_tahun'=> true,
            ],

            // Keuangan
            [
                'table'    => 'rt_fact_tunggakan',
                'label'    => 'Tunggakan',
                'icon'     => 'file-text',
                'kategori' => 'Keuangan',
                'pk'       => 'id',
                'has_tahun'=> true,
            ],
            [
                'table'    => 'rt_fact_p2tl',
                'label'    => 'P2TL (Penertiban Pemakaian Tenaga Listrik)',
                'icon'     => 'alert-triangle',
                'kategori' => 'Keuangan',
                'pk'       => 'id',
                'has_tahun'=> true,
            ],

            // Log
            [
                'table'    => 'rt_import_logs',
                'label'    => 'Import Logs',
                'icon'     => 'upload',
                'kategori' => 'Log',
                'pk'       => 'id',
                'has_tahun'=> true,
            ],
        ];
    }

    /**
     * GET /api/stats/database
     * Mengembalikan jumlah record tiap tabel RT beserta info tahun yang tersedia.
     */
    public function index(): JsonResponse
    {
        $stats = [];

        foreach ($this->tableMap() as $item) {
            $table = $item['table'];

            try {
                $total = DB::table($table)->count();

                $tahunList = [];
                if ($item['has_tahun']) {
                    $tahunList = DB::table($table)
                        ->select('tahun')
                        ->distinct()
                        ->orderByDesc('tahun')
                        ->pluck('tahun')
                        ->toArray();
                }

                // Hitung record tahun ini
                $tahunIni = now()->year;
                $recordTahunIni = $item['has_tahun']
                    ? DB::table($table)->where('tahun', $tahunIni)->count()
                    : null;

                // updated_at terbaru
                $latestUpdated = null;
                try {
                    $latestUpdated = DB::table($table)
                        ->max('updated_at');
                } catch (\Exception $e) {
                    // tabel tidak punya timestamps — abaikan
                }

                $stats[] = [
                    'table'            => $table,
                    'label'            => $item['label'],
                    'icon'             => $item['icon'],
                    'kategori'         => $item['kategori'],
                    'total_records'    => $total,
                    'record_tahun_ini' => $recordTahunIni,
                    'tahun_tersedia'   => $tahunList,
                    'latest_updated'   => $latestUpdated,
                    'status'           => $total > 0 ? 'ada_data' : 'kosong',
                ];
            } catch (\Exception $e) {
                $stats[] = [
                    'table'            => $table,
                    'label'            => $item['label'],
                    'icon'             => $item['icon'],
                    'kategori'         => $item['kategori'],
                    'total_records'    => null,
                    'record_tahun_ini' => null,
                    'tahun_tersedia'   => [],
                    'latest_updated'   => null,
                    'status'           => 'error',
                    'error'            => $e->getMessage(),
                ];
            }
        }

        // Ringkasan global
        $summary = [
            'total_tabel'       => count($stats),
            'tabel_ada_data'    => collect($stats)->where('status', 'ada_data')->count(),
            'tabel_kosong'      => collect($stats)->where('status', 'kosong')->count(),
            'tabel_error'       => collect($stats)->where('status', 'error')->count(),
            'total_semua_record'=> collect($stats)->sum('total_records'),
            'generated_at'      => now()->toISOString(),
        ];

        return $this->success([
            'summary' => $summary,
            'tables'  => $stats,
        ]);
    }
}
