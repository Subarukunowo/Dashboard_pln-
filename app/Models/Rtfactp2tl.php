<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RtFactP2tl extends Model
{
    protected $table = 'rt_fact_p2tl';

    protected $fillable = [
        'tahun',
        'bulan',
        'is_triwulan',
        'is_akumulasi',
        'tipe',
        'jumlah_temuan',
        'nilai_tagihan_rp',
        'sumber',
    ];

    protected $casts = [
        'tahun'           => 'integer',
        'bulan'           => 'integer',
        'is_triwulan'     => 'boolean',
        'is_akumulasi'    => 'boolean',
        'jumlah_temuan'   => 'integer',
        'nilai_tagihan_rp' => 'decimal:6',
    ];
}