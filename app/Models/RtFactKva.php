<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RtFactKva extends Model
{
    protected $table = 'rt_fact_kva';

    protected $fillable = [
        'tahun', 'bulan', 'is_triwulan', 'is_akumulasi',
        'tipe', 'sub_golongan_kode', 'golongan_kode',
        'nilai_kva', 'sumber',
    ];

    protected $casts = [
        'tahun'        => 'integer',
        'bulan'        => 'integer',
        'is_triwulan'  => 'boolean',
        'is_akumulasi' => 'boolean',
        'nilai_kva'    => 'float',
    ];
}