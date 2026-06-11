<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RtDimSubGolongan extends Model
{
    protected $table = 'rt_dim_sub_golongan';

    protected $primaryKey = 'kode';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode',
        'nama',
        'golongan_kode',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];
}