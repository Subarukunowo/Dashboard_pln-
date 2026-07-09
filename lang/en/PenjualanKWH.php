<?php

$tahunIni  = now()->year;
$tahunLalu = $tahunIni - 1;

return [
    'title'              => "KWH Sales — Target vs Realization {$tahunLalu} & {$tahunIni}",
    'delta_title'        => 'Target Variance (GWh)',
    'pencapaian_title'   => '% Achievement Against Target',
    'pertumbuhan_title'  => "% Growth {$tahunLalu} vs {$tahunIni}",
    'ringkasan_title'    => 'Summary Table (GWh)',

    'filter' => [
        'semua'     => 'All Months',
        'bulanan'   => 'Monthly',
        'akumulasi' => 'Cumulative',
    ],

    'table' => [
        'bulan'           => 'Month',
        'selisih'         => 'Variance',
        'selisih_gwh'     => 'Variance (GWh)',
        'selisih_pct'     => '% Variance',
        'delta_gwh'       => 'Delta GWh',
        'target_lalu'     => "Target {$tahunLalu}",
        'real_lalu'       => "Actual {$tahunLalu}",
        'target_ini'      => "Target {$tahunIni}",
        'realisasi_ini'   => "Actual {$tahunIni}",
        't_lalu'          => "T'" . substr((string) $tahunLalu, -2),
        'r_lalu'          => "A'" . substr((string) $tahunLalu, -2),
        't_ini'           => "T'" . substr((string) $tahunIni, -2),
        'r_ini'           => "A'" . substr((string) $tahunIni, -2),
        'pct_pencapaian'  => '% Achievement',
        'pct_pertumbuhan' => '% Growth',
    ],
];