<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RtFactPenjualan extends Model
{
    protected $table = 'rt_fact_penjualan';

    protected $fillable = [
        'tahun',
        'bulan',
        'is_triwulan',
        'is_akumulasi',
        'tipe',
        'sub_golongan_kode',
        'golongan_kode',
        'nilai_kwh',
        'nilai_kwh_lalu',
        'pct_pencapaian',
        'growth_yoy',
        'sumber',
    ];

    protected $casts = [
        'tahun'          => 'integer',
        'bulan'          => 'integer',
        'is_triwulan'    => 'boolean',
        'is_akumulasi'   => 'boolean',
        'nilai_kwh'      => 'decimal:6',
        'nilai_kwh_lalu' => 'decimal:6',
        'pct_pencapaian' => 'decimal:6',
        'growth_yoy'     => 'decimal:6',
    ];
}