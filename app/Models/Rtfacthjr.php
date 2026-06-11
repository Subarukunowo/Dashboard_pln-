<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RtFactHjr extends Model
{
    protected $table = 'rt_fact_hjr';

    protected $fillable = [
        'tahun',
        'bulan',
        'is_triwulan',
        'is_akumulasi',
        'tipe',
        'tarif_kode',
        'tegangan',
        'nilai_rpkwh',
        'sumber',
    ];

    protected $casts = [
        'tahun'        => 'integer',
        'bulan'        => 'integer',
        'is_triwulan'  => 'boolean',
        'is_akumulasi' => 'boolean',
        'nilai_rpkwh'  => 'decimal:6',
    ];
}