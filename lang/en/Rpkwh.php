<?php

$tahunIni  = now()->year;
$tahunLalu = $tahunIni - 1;

return [
    'title'               => 'RPKWH Plan vs Realization',
    'delta_title'         => 'RPKWH Variance (Rp/kWh)',
    'pie_title'           => 'RPKWH Per Group',
    'growth_title'        => 'RPKWH Realization Growth Per Group (%)',
    'bpp_hjr_title'       => 'BPP HJR RPKWH Realization (Rp/kWh)',
    'analisa_title'       => "RPKWH Analysis {$tahunLalu} vs {$tahunIni} (Rp/kWh)",
    'delta_analisa_title' => 'RPKWH Analysis Variance (Rp/kWh)',
    'persen_title'        => '% RPKWH Realization',
    'tabel_analisa_title' => 'RPKWH Analysis Table',

    'filter' => [
        'semua'     => 'All Months',
        'bulanan'   => 'Monthly',
        'akumulasi' => 'Cumulative',
    ],

    'table' => [
        'bulan'             => 'Month',
        'golongan'          => 'Group',
        'nilai'             => 'Value',
        'rencana'           => 'Plan',
        'realisasi'         => 'Actual',
        'target'            => 'Target',
        'selisih'           => 'Variance',
        'selisih_rpkwh'     => 'Variance (Rp/kWh)',
        'selisih_pct'       => '% Variance',
        'hjr'               => 'HJR (Rp/kWh)',
        'bpp'               => 'BPP (Rp/kWh)',
        'target_lalu'       => "Target {$tahunLalu}",
        'real_lalu'         => "Actual {$tahunLalu}",
        'target_ini'        => "Target {$tahunIni}",
        'realisasi_ini'     => "Actual {$tahunIni}",
        't_lalu'            => "T'" . substr((string) $tahunLalu, -2),
        'r_lalu'            => "A'" . substr((string) $tahunLalu, -2),
        't_ini'             => "T'" . substr((string) $tahunIni, -2),
        'r_ini'             => "A'" . substr((string) $tahunIni, -2),
        'pct_capai_target'  => '% Target Achievement',
        'pct_capai_real'    => '% Actual Achievement',
        'sosial'            => 'Social',
        'rtangga'           => 'Household',
        'bisnis'            => 'Business',
        'industri'          => 'Industry',
        'publik'            => 'Public',
        'multiguna'         => 'Multipurpose',
    ],
];