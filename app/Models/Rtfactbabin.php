<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RtFactBabin extends Model
{
    protected $table = 'rt_fact_babin';

    protected $fillable = [
        'tahun',
        'bulan',
        'is_akumulasi',
        'area',
        'tipe',
        'nilai_kwh',
        'nilai_rp',
        'sumber',
    ];

    protected $casts = [
        'tahun'        => 'integer',
        'bulan'        => 'integer',
        'is_akumulasi' => 'boolean',
        'nilai_kwh'    => 'decimal:6',
        'nilai_rp'     => 'decimal:6',
    ];
}