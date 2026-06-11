<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RtFactPelanggan extends Model
{
    protected $table = 'rt_fact_pelanggan';

    protected $fillable = [
        'tahun',
        'bulan',
        'is_triwulan',
        'is_akumulasi',
        'tipe',
        'sub_golongan_kode',
        'golongan_kode',
        'nilai_pelanggan',
        'nilai_kva',
        'pct_pencapaian',
        'sumber',
    ];

    protected $casts = [
        'tahun'           => 'integer',
        'bulan'           => 'integer',
        'is_triwulan'     => 'boolean',
        'is_akumulasi'    => 'boolean',
        'nilai_pelanggan' => 'decimal:6',
        'nilai_kva'       => 'decimal:6',
        'pct_pencapaian'  => 'decimal:6',
    ];
}