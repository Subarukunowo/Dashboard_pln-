<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RtFactLosses extends Model
{
    protected $table = 'rt_fact_losses';

    protected $fillable = [
        'tahun',
        'bulan',
        'is_triwulan',
        'is_akumulasi',
        'tipe_losses',
        'tipe',
        'nilai_pct',
        'nilai_kwh',
        'sumber',
    ];

    protected $casts = [
        'tahun'        => 'integer',
        'bulan'        => 'integer',
        'is_triwulan'  => 'boolean',
        'is_akumulasi' => 'boolean',
        'nilai_pct'    => 'decimal:6',
        'nilai_kwh'    => 'decimal:6',
    ];
}