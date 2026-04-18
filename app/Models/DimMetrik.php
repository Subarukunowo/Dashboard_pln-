<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimMetrik extends Model
{
    protected $table      = 'dim_metrik';
    protected $primaryKey = 'kode';
    public    $incrementing = false;
    public    $timestamps   = false;
    protected $keyType    = 'string';

    protected $fillable = ['kode', 'nama', 'satuan', 'kategori'];

    public function facts()
    {
        return $this->hasMany(FactHistoris::class, 'metrik_kode', 'kode');
    }
}