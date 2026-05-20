<?php

return [
    'title'        => 'PLN Batam Network Losses 2025',
    'pct_title'    => 'Losses % (Monthly & Cumulative)',
    'kwh_title'    => 'Transmission, Distribution & Network Losses (kWh)',
    'detail_title' => 'Losses Detail Table (million kWh)',

    'filter' => [
        'semua'       => 'All Months',
        'dengan_emin' => 'With EMIN',
        'tanpa_emin'  => 'Without EMIN',
    ],

    'table' => [
        'bulan'            => 'Month',
        'prod_bruto'       => 'Gross Output',
        'prod_netto'       => 'Net Output',
        'siap_salur'       => 'Ready to Distribute',
        'losses_trans'     => 'Trans Losses',
        'losses_trans_bln' => 'Trans Losses Monthly (%)',
        'losses_trans_akum'=> 'Trans Losses Cumul (%)',
        'losses_bln'       => 'Losses Monthly (%)',
        'losses_akum'      => 'Losses Cumul (%)',
        'interkoneksi'     => 'Interconnection',
        'losses_dist'      => 'Dist Losses',
        'losses_dist_pct'  => 'Dist Losses (%)',
        'losses_dist_akum' => 'Dist Losses Cumul',
        'losses_jar'       => 'Network Losses',
        'losses_jar_bln'   => 'Network Losses Monthly (%)',
        'losses_jar_akum'  => 'Network Losses Cumul (%)',
        'losses_jar_pct'   => 'Network Losses (%)',
        'target'           => 'Target (%)',
    ],
];