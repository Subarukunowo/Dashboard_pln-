<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RtFactP2tl extends Model
{
    protected $table = 'rt_fact_p2tl';

  protected $fillable = [
    'tahun', 'bulan', 'is_triwulan', 'is_akumulasi', 'tipe',
    'jumlah_pemeriksaan', 'jumlah_temuan', 'temuan_kwh', 'nilai_tagihan_rp', 'sumber',
];

protected $casts = [
    'tahun' => 'integer',
    'bulan' => 'integer',
    'is_triwulan' => 'boolean',
    'is_akumulasi' => 'boolean',
    'jumlah_pemeriksaan' => 'integer',
    'jumlah_temuan' => 'integer',
    'temuan_kwh' => 'float',
    'nilai_tagihan_rp' => 'float',
];
}