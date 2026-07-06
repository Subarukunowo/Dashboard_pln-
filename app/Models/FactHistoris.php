<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactHistoris extends Model
{
    protected $table = 'fact_historis';

protected $fillable = [
    'tahun',
    'bulan',
    'is_triwulan',
    'is_akumulasi',
    'is_jumlah_tahunan',
    'golongan_kode',
    'metrik_kode',      // ✅ Pastikan ini ada!
    'nilai',
    'satuan',
    'sumber',
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
    /**
     * Relasi ke DimMetrik
     */
    public function metrik(): BelongsTo
    {
        return $this->belongsTo(DimMetrik::class, 'metrik_kode', 'kode');
    }

    /**
     * Relasi ke DimGolongan
     */
    public function golongan(): BelongsTo
    {
        return $this->belongsTo(DimGolongan::class, 'golongan_kode', 'kode');
    }

    /**
     * Relasi ke DimPeriode
     */
    public function periode(): BelongsTo
    {
        return $this->belongsTo(DimPeriode::class, 'tahun', 'tahun');
    }
    /**
     * Scope untuk filter metrik
     */
    public function scopeMetrik($query, string $metrikKode)
    {
        return $query->where('metrik_kode', strtolower($metrikKode));
    }
}