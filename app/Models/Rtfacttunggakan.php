<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RtFactTunggakan extends Model
{
    protected $table = 'rt_fact_tunggakan';

    protected $fillable = [
        'tahun', 'bulan', 'pal_berjalan', 'pal_tunggakan',
        'ts_berjalan', 'ts_tunggakan', 'jumlah_tunggakan',
        'pendapatan', 'ratio_tunggakan_pct', 'sumber',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'bulan' => 'integer',
        'pal_berjalan' => 'float',
        'pal_tunggakan' => 'float',
        'ts_berjalan' => 'float',
        'ts_tunggakan' => 'float',
        'jumlah_tunggakan' => 'float',
        'pendapatan' => 'float',
        'ratio_tunggakan_pct' => 'float',
    ];
}