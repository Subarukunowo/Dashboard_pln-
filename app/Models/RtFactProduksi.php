<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RtFactProduksi extends Model
{
    protected $table = 'rt_fact_produksi';

    protected $fillable = [
        'tahun',
        'bulan',
        'tipe_losses',          // baru: 'dengan_emin' | 'tanpa_emin'
        'prod_bruto',
        'ps_kit',
        'prod_netto',
        'loko_transmisi',
        'siap_salur_transmisi',
        'ps_gi',
        'prod_siap_salur',
        'interkoneksi',
        'loko_distribusi',
        'ps_distribusi',
        'blkg_padang',
        'sembulang',
        'buluh',
        'p_nguan',
        'penjualan_tul3_09',    // baru: dibutuhkan hitung losses distribusi & jaringan
        'emin_sistem',          // baru: referensi, hanya ada di sheet tanpa_emin
        'sumber',
    ];

    protected $casts = [
        'prod_bruto'            => 'decimal:6',
        'ps_kit'                => 'decimal:6',
        'prod_netto'            => 'decimal:6',
        'loko_transmisi'        => 'decimal:6',
        'siap_salur_transmisi'  => 'decimal:6',
        'ps_gi'                 => 'decimal:6',
        'prod_siap_salur'       => 'decimal:6',
        'interkoneksi'          => 'decimal:6',
        'loko_distribusi'       => 'decimal:6',
        'ps_distribusi'         => 'decimal:6',
        'blkg_padang'           => 'decimal:6',
        'sembulang'             => 'decimal:6',
        'buluh'                 => 'decimal:6',
        'p_nguan'               => 'decimal:6',
        'penjualan_tul3_09'     => 'decimal:6',
        'emin_sistem'           => 'decimal:6',
    ];
}