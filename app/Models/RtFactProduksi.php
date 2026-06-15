<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RtFactProduksi extends Model
{
    protected $table = 'rt_fact_produksi';

    protected $fillable = [
        'tahun', 'bulan', 'prod_bruto', 'ps_kit', 'prod_netto',
        'loko_transmisi', 'siap_salur_transmisi', 'ps_gi',
        'prod_siap_salur', 'interkoneksi', 'loko_distribusi', 'ps_distribusi',
        'blkg_padang', 'sembulang', 'buluh', 'p_nguan', 'sumber',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'bulan' => 'integer',
        'prod_bruto' => 'float',
        'ps_kit' => 'float',
        'prod_netto' => 'float',
        'loko_transmisi' => 'float',
        'siap_salur_transmisi' => 'float',
        'ps_gi' => 'float',
        'prod_siap_salur' => 'float',
        'interkoneksi' => 'float',
        'loko_distribusi' => 'float',
        'ps_distribusi' => 'float',
        'blkg_padang' => 'float',
        'sembulang' => 'float',
        'buluh' => 'float',
        'p_nguan' => 'float',
    ];
}