<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Dimension\{DimTahun, DimPeriode, DimGolonganTarif, DimMetrik};

class FactPenjualanBatam extends Model
{
    protected $table = 'fact_penjualan_batam';
    protected $guarded = ['id'];
    public $timestamps = false;

    protected $casts = [
        'nilai_aktual' => 'decimal:2',
        'nilai_rkap' => 'decimal:2',
        'yoy_pertumbuhan' => 'decimal:2',
    ];

    /**
     * Relasi ke dimension tables
     */
    public function tahun(): BelongsTo
    {
        return $this->belongsTo(DimTahun::class, 'dim_tahun_tahun', 'tahun');
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(DimPeriode::class, 'dim_periode_id');
    }

    public function golongan(): BelongsTo
    {
        return $this->belongsTo(DimGolonganTarif::class, 'dim_golongan_tarif_id');
    }

    public function metrik(): BelongsTo
    {
        return $this->belongsTo(DimMetrik::class, 'dim_metrik_id');
    }

    /**
     * Scopes untuk query yang lebih fleksibel
     */
    public function scopeByTahun(Builder $query, int $tahun): Builder
    {
        return $query->where('dim_tahun_tahun', $tahun);
    }

    public function scopeByTahunRange(Builder $query, int $start, int $end): Builder
    {
        return $query->whereBetween('dim_tahun_tahun', [$start, $end]);
    }

    public function scopeByGolongan(Builder $query, array $kodeGolongan): Builder
    {
        return $query->whereHas('golongan', function ($q) use ($kodeGolongan) {
            $q->whereIn('kode', $kodeGolongan);
        });
    }

    public function scopeByGolonganKode(Builder $query, string $kode): Builder
    {
        return $query->whereHas('golongan', function ($q) use ($kode) {
            $q->where('kode', $kode);
        });
    }

    public function scopeByMetrik(Builder $query, string $kodeMetrik): Builder
    {
        return $query->whereHas('metrik', function ($q) use ($kodeMetrik) {
            $q->where('kode', $kodeMetrik);
        });
    }

    public function scopeByPeriode(Builder $query, string $kodePeriode): Builder
    {
        return $query->whereHas('periode', function ($q) use ($kodePeriode) {
            $q->where('kode', $kodePeriode);
        });
    }

    public function scopeByTipePeriode(Builder $query, string $tipe): Builder
    {
        return $query->whereHas('periode', function ($q) use ($tipe) {
            $q->where('tipe', $tipe);
        });
    }

    /**
     * Scope untuk mendapatkan data aktual saja
     */
    public function scopeAktual(Builder $query): Builder
    {
        return $query->whereNotNull('nilai_aktual');
    }

    /**
     * Scope untuk mendapatkan data RKAP saja
     */
    public function scopeRkap(Builder $query): Builder
    {
        return $query->whereNotNull('nilai_rkap');
    }

    /**
     * Hitung pencapaian (%)
     */
    public function getPencapaianAttribute(): ?float
    {
        if ($this->nilai_rkap && $this->nilai_rkap > 0) {
            return round(($this->nilai_aktual / $this->nilai_rkap) * 100, 2);
        }
        return null;
    }
}