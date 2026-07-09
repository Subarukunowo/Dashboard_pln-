<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RtFactTunggakan extends Model
{
    protected $table = 'rt_fact_tunggakan';

    protected $fillable = [
    'tahun',
    'bulan',
    'tipe',
    'pal',
    'pal_berjalan',
    'pal_tunggakan',
    'tagihan_susulan',
    'ts_berjalan',
    'ts_tunggakan',
    'jumlah_tunggakan',
    'pendapatan',
    'ratio_pct',
    'sumber',
];
 
    protected $casts = [
        'tahun' => 'integer',
        'bulan' => 'integer',
        'pal' => 'float',
        'tagihan_susulan' => 'float',
        'jumlah_tunggakan' => 'float',
        'pendapatan' => 'float',
        'ratio_pct' => 'float',
    ];
}