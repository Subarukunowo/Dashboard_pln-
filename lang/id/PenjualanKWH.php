<?php

$tahunIni  = now()->year;
$tahunLalu = $tahunIni - 1;

return [
    'title'               => 'Rencana vs Realisasi RPKWH',
    'delta_title'         => 'Selisih RPKWH (Rp/kWh)',
    'pie_title'           => 'RPKWH Per Golongan',
    'growth_title'        => 'Realisasi Growth RPKWH Per Golongan (%)',
    'bpp_hjr_title'       => 'Realisasi BPP HJR RPKWH (Rp/kWh)',
    'analisa_title'       => "Analisa RPKWH {$tahunLalu} vs {$tahunIni} (Rp/kWh)",
    'delta_analisa_title' => 'Selisih Analisa RPKWH (Rp/kWh)',
    'persen_title'        => '% Realisasi RPKWH',
    'tabel_analisa_title' => 'Tabel Analisa RPKWH',

    'filter' => [
        'semua'     => 'Semua Bulan',
        'bulanan'   => 'Bulanan',
        'akumulasi' => 'Akumulasi',
    ],

    'table' => [
        'bulan'             => 'Bulan',
        'golongan'          => 'Golongan',
        'nilai'             => 'Nilai',
        'rencana'           => 'Rencana',
        'realisasi'         => 'Realisasi',
        'target'            => 'Target',
        'selisih'           => 'Selisih',
        'selisih_rpkwh'     => 'Selisih (Rp/kWh)',
        'selisih_pct'       => '% Selisih',
        'hjr'               => 'HJR (Rp/kWh)',
        'bpp'               => 'BPP (Rp/kWh)',
        'target_lalu'       => "Target {$tahunLalu}",
        'real_lalu'         => "Realisasi {$tahunLalu}",
        'target_ini'        => "Target {$tahunIni}",
        'realisasi_ini'     => "Realisasi {$tahunIni}",
        't_lalu'            => "T'" . substr((string) $tahunLalu, -2),
        'r_lalu'            => "R'" . substr((string) $tahunLalu, -2),
        't_ini'             => "T'" . substr((string) $tahunIni, -2),
        'r_ini'             => "R'" . substr((string) $tahunIni, -2),
        'pct_capai_target'  => '% Capai Target',
        'pct_capai_real'    => '% Capai Real',
        'sosial'            => 'Sosial',
        'rtangga'           => 'R. Tangga',
        'bisnis'            => 'Bisnis',
        'industri'          => 'Industri',
        'publik'            => 'Publik',
        'multiguna'         => 'Multiguna',
    ],
];