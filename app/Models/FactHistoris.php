<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactHistoris extends Model
{
    protected $table = 'fact_historis';

    protected $fillable = [
        'tahun', 'bulan', 'triwulan', 'golongan_kode',
        'jenis_data', 'tipe_nilai', 'nilai', 'satuan', 'sumber',
    ];

    protected $casts = [
        'nilai'    => 'decimal:6',
        'tahun'    => 'integer',
        'bulan'    => 'integer',
        'triwulan' => 'integer',
    ];

    public function scopeTahun($q, $tahun)   { return $q->where('tahun', $tahun); }
    public function scopeJenis($q, $jenis)   { return $q->where('jenis_data', $jenis); }
    public function scopeGolongan($q, $kode) { return $q->where('golongan_kode', $kode); }
    public function scopeBulanan($q)         { return $q->where('tipe_nilai', 'bulanan'); }
}