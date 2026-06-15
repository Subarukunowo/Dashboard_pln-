<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RtFactRpkwh extends Model
{
    protected $table = 'rt_fact_rpkwh';

    protected $fillable = [
        'tahun', 'bulan', 'is_triwulan', 'is_akumulasi', 'tipe',
        'sub_golongan_kode', 'golongan_kode',
        'nilai_rpkwh', 'nilai_rpkwh_lalu', 'pct_pencapaian', 'sumber',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'bulan' => 'integer',
        'is_triwulan' => 'boolean',
        'is_akumulasi' => 'boolean',
        'nilai_rpkwh' => 'float',
        'nilai_rpkwh_lalu' => 'float',
        'pct_pencapaian' => 'float',
    ];
}