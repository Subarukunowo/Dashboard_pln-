<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RtFactJamNyala extends Model
{
    protected $table = 'rt_fact_jam_nyala';

    protected $fillable = [
        'tahun',
        'bulan',
        'is_triwulan',
        'is_akumulasi',
        'tipe',
        'golongan_kode',
        'nilai_jam',
        'pct_pencapaian',
        'sumber',
    ];

    protected $casts = [
        'tahun'          => 'integer',
        'bulan'          => 'integer',
        'is_triwulan'    => 'boolean',
        'is_akumulasi'   => 'boolean',
        'nilai_jam'      => 'decimal:2',
        'pct_pencapaian' => 'decimal:6',
    ];
}