<?php

return [
    'title' => 'BPP Rp/kWh Realization: Consolidation vs Internal',

    'kategori' => [
        'pembangkit'    => 'Generation BPP',
        'transmisi'     => 'BPP up to Transmission',
        'distribusi_tm' => 'BPP up to Distribution MV',
        'distribusi_tr' => 'BPP up to Distribution LV',
    ],

    'filter' => [
        'kategori'    => 'Filter BPP category',
        'bulan'       => 'Filter month',
        'semua_bulan' => 'All Months',
    ],

    'table' => [
        'bulan'       => 'Month',
        'konsolidasi' => 'Consolidation',
        'internal'    => 'Internal',
        'selisih'     => 'Variance',
    ],
];