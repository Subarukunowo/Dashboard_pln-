<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RtFactBpp extends Model
{
    protected $table = 'rt_fact_bpp';

    protected $fillable = [
        'tahun',
        'bulan',
        'versi',
        'jenis_bpp',
        'nilai_rpkwh',
        'sumber',
    ];

    protected $casts = [
        'tahun'       => 'integer',
        'bulan'       => 'integer',
        'nilai_rpkwh' => 'decimal:6',
    ];
}