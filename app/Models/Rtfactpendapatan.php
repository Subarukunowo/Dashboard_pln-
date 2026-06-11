<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RtFactPendapatan extends Model
{
    protected $table = 'rt_fact_pendapatan';

    protected $fillable = [
        'tahun',
        'bulan',
        'is_triwulan',
        'is_akumulasi',
        'tipe',
        'sub_golongan_kode',
        'golongan_kode',
        'nilai_rp',
        'nilai_rp_lalu',
        'pct_pencapaian',
        'sumber',
    ];

    protected $casts = [
        'tahun'          => 'integer',
        'bulan'          => 'integer',
        'is_triwulan'    => 'boolean',
        'is_akumulasi'   => 'boolean',
        'nilai_rp'       => 'decimal:6',
        'nilai_rp_lalu'  => 'decimal:6',
        'pct_pencapaian' => 'decimal:6',
    ];
}