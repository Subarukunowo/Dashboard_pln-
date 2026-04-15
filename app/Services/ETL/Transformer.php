<?php

namespace App\Services\ETL;

class Transformer
{
    // Mapping ke kode standar di database (dim_golongan)
    private const GOLONGAN_MAP = [
        'sosial'                 => 'S',
        's (sosial)'             => 'S',
        'residensial'            => 'R',
        'rumahtangga'            => 'R',
        'rumah tangga'           => 'R',
        'r (residensial)'        => 'R',
        'bisnis'                 => 'B',
        'b (bisnis)'             => 'B',
        'industri'               => 'I',
        'i (industri)'           => 'I',
        'publik'                 => 'P',
        'pemerintah'             => 'P',
        'publik/ pemerintah'     => 'P',
        'p (publik/ pemerintah)' => 'P',
        'multiguna'              => 'M',
        'multiguna / prabayar'   => 'M',
        'm (multiguna)'          => 'M',
        'jumlah'                 => 'TOTAL', // Diubah dari JUMLAH ke TOTAL
        'total'                  => 'TOTAL',
    ];

    private const BULAN_MAP = [
        'jan'=>1,'feb'=>2,'mar'=>3,'apr'=>4,'may'=>5,'mei'=>5,
        'jun'=>6,'jul'=>7,'aug'=>8,'agu'=>8,'sep'=>9,
        'oct'=>10,'okt'=>10,'nov'=>11,'dec'=>12,'des'=>12,
    ];

  private const JENIS_MAP = [
    'kwh jual'            => 'kwh_jual',
    'mwh jual'            => 'mwh_jual',
    'rupiah penjualan'    => 'rupiah',      // Gunakan 'rupiah' (kecil) sesuai dim_metrik
    'pelanggan'           => 'pelanggan',
    'daya tersambung'     => 'daya_va',     // Gunakan 'daya_va' sesuai dim_metrik
    'rp/kwh jual'         => 'rpkwh',       // Gunakan 'rpkwh' sesuai dim_metrik
    // Tambahkan mapping untuk variasi kapital jika diperlukan
    'PENJUALAN'           => 'kwh_jual',    // Diarahkan ke kwh_jual agar konsisten
    'PENDAPATAN'          => 'rupiah',      // Diarahkan ke rupiah agar konsisten
    'PELANGGAN'           => 'pelanggan',
];

    /**
     * Transformasi data 1997-2004 (format triwulan)
     */
    public function transformTriwulan(array $sheets, int $tahun): array
    {
        $records      = [];
        $currentJenis = null;
        $twToBulan    = [1 => 3, 2 => 6, 3 => 9, 4 => 12];

        foreach ($sheets as $sheet) {
            foreach ($sheet as $row) {
                $jenisDeteksi = $this->detectJenis($row);
                if ($jenisDeteksi) { $currentJenis = $jenisDeteksi; continue; }
                if (!$currentJenis) continue;

                $golongan = $this->normalizeGolongan($row[0] ?? '');
                if (!$golongan) continue;

                foreach ($twToBulan as $tw => $bulan) {
                    $nilai = $this->parseAngka($row[$tw] ?? null);
                    if ($nilai === null) continue;

                    $records[] = [
                        'tahun'             => $tahun,
                        'bulan'             => $bulan,
                        'is_triwulan'       => true,
                        'is_akumulasi'      => false,
                        'is_jumlah_tahunan' => false,
                        'golongan_kode'     => $golongan,
                        'metrik_kode'       => $currentJenis, // Sinkron dengan fact_historis
                        'nilai'             => $nilai,
                        'satuan'            => $this->getSatuan($currentJenis),
                        'sumber'            => 'import',
                    ];
                }

                $jumlah = $this->parseAngka($row[5] ?? null);
                if ($jumlah !== null) {
                    $records[] = [
                        'tahun'             => $tahun,
                        'bulan'             => 12,
                        'is_triwulan'       => false,
                        'is_akumulasi'      => false,
                        'is_jumlah_tahunan' => true,
                        'golongan_kode'     => $golongan,
                        'metrik_kode'       => $currentJenis,
                        'nilai'             => $jumlah,
                        'satuan'            => $this->getSatuan($currentJenis),
                        'sumber'            => 'import',
                    ];
                }
            }
        }
        return $records;
    }

    /**
     * Transformasi data 2005-2023 (format bulanan)
     */
    public function transformBulanan(array $sheets, int $tahun): array
    {
        $records      = [];
        $currentJenis = null;
        $headerRow    = null;

        foreach ($sheets as $sheet) {
            foreach ($sheet as $row) {
                $jenisDeteksi = $this->detectJenis($row);
                if ($jenisDeteksi) {
                    $currentJenis = $jenisDeteksi;
                    $headerRow    = null;
                    continue;
                }

                if (!$currentJenis) continue;

                if ($this->isHeaderRow($row, $tahun)) {
                    $headerRow = $row;
                    continue;
                }

                if (!$headerRow) continue;

                $golongan = $this->normalizeGolongan($row[0] ?? '');
                if (!$golongan) continue;

                foreach ($headerRow as $colIdx => $headerVal) {
                    if ($colIdx === 0) continue;

                    $bulan = $this->parseBulanFromHeader((string)$headerVal);

                    if ($bulan) {
                        $nilai = $this->parseAngka($row[$colIdx] ?? null);
                        if ($nilai === null) continue;

                        $records[] = [
                            'tahun'         => $tahun,
                            'bulan'         => $bulan,
                            'golongan_kode' => $golongan,
                            'metrik_kode'   => $currentJenis,
                            'nilai'         => $nilai,
                            'satuan'        => $this->getSatuan($currentJenis),
                            'sumber'        => 'import_excel',
                        ];
                    } elseif (str_contains(strtolower((string)$headerVal), 'jumlah')) {
                        $nilai = $this->parseAngka($row[$colIdx] ?? null);
                        if ($nilai !== null) {
                            $records[] = [
                                'tahun'         => $tahun,
                                'bulan'         => 12, // Default ke Des untuk record tahunan
                                'golongan_kode' => $golongan,
                                'metrik_kode'   => $currentJenis,
                                'nilai'         => $nilai,
                                'satuan'        => $this->getSatuan($currentJenis),
                                'sumber'        => 'import_excel',
                            ];
                        }
                    }
                }
            }
        }
        return $records;
    }

    /**
     * Transformasi data 2024
     */
    public function transform2024(array $sheets, int $tahun): array
    {
        $records = $this->transformBulanan($sheets, $tahun);

        foreach ($sheets as $sheet) {
            foreach ($sheet as $row) {
                if ($this->isHeaderRowDelta($row)) continue;

                $golongan = $this->normalizeGolongan($row[0] ?? '');
                if (!$golongan) continue;

                $jenisList = [
                    1 => ['jenis' => 'kwh_jual',       'tahun_override' => 2023],
                    2 => ['jenis' => 'kwh_jual',       'tahun_override' => 2024],
                    3 => ['jenis' => 'delta_gwh',      'tahun_override' => null],
                    4 => ['jenis' => 'growth',         'tahun_override' => null],
                    5 => ['jenis' => 'komposisi',      'tahun_override' => null],
                ];

                foreach ($jenisList as $col => $config) {
                    $nilai = $this->parseAngka($row[$col] ?? null);
                    if ($nilai === null) continue;

                    $records[] = [
                        'tahun'         => $config['tahun_override'] ?? $tahun,
                        'bulan'         => 12,
                        'golongan_kode' => $golongan,
                        'metrik_kode'   => $config['jenis'],
                        'nilai'         => $nilai,
                        'satuan'        => ($config['jenis'] === 'kwh_jual' || $config['jenis'] === 'delta_gwh') ? 'GWh' : '%',
                        'sumber'        => 'import_excel',
                    ];
                }
            }
        }
        return $records;
    }

    // --- Helpers ---

    private function detectJenis(array $row): ?string
    {
        foreach ($row as $cell) {
            $lower = strtolower(trim((string)$cell));
            if (isset(self::JENIS_MAP[$lower])) {
                return self::JENIS_MAP[$lower];
            }
        }
        return null;
    }

    private function normalizeGolongan(?string $val): ?string
    {
        if (!$val) return null;
        $lower = strtolower(trim($val));
        return self::GOLONGAN_MAP[$lower] ?? null;
    }

    private function isHeaderRow(array $row, int $tahun): bool
    {
        $shortYear = substr((string)$tahun, 2);
        foreach ($row as $cell) {
            $lower = strtolower(trim((string)$cell));
            if (preg_match('/^(jan|feb|mar|apr|may|mei|jun|jul|aug|agu|sep|oct|okt|nov|dec|des)-?' . $shortYear . '$/i', $lower)) {
                return true;
            }
        }
        return false;
    }

    private function isHeaderRowDelta(array $row): bool
    {
        foreach ($row as $cell) {
            if (str_contains(strtolower((string)$cell), 'gwh 202')) return true;
        }
        return false;
    }

    private function parseBulanFromHeader(string $val): ?int
    {
        preg_match('/^([a-zA-Z]+)[-\s]?\d{2,4}$/', trim($val), $m);
        if (!$m) return null;
        $key = strtolower($m[1]);
        return self::BULAN_MAP[$key] ?? null;
    }

    public function parseAngka($val): ?float
    {
        if ($val === null || $val === '' || $val === '-' || $val === '#N/A') return null;
        if (is_numeric($val)) return (float)$val;
        $clean = str_replace(['.', ' '], '', (string)$val);
        $clean = str_replace(',', '.', $clean);
        $clean = preg_replace('/[^0-9.\-]/', '', $clean);
        return is_numeric($clean) ? (float)$clean : null;
    }

   private function getSatuan(string $jenis): string
{
    return match($jenis) {
        'kwh_jual'        => 'KWH',
        'mwh_jual'        => 'MWH',
        'rupiah'          => 'Rp',      // Diubah dari rupiah_penjualan
        'pelanggan'       => 'pelanggan',
        'daya_va'         => 'VA',      // Diubah dari daya_tersambung
        'rpkwh'           => 'Rp/kWh',  // Diubah dari rpkwh_jual
        'pertumbuhan_yoy', 
        'growth', 
        'komposisi'       => '%',
        'delta_gwh'       => 'GWh',
        default           => '',
    };
}
}