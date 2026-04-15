<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimPeriode extends Model
{
    protected $table = 'dim_periode';
    
    // Beritahu Laravel kalau primary key-nya adalah 'tahun', bukan 'id'
    protected $primaryKey = 'tahun';
    
    // Beritahu Laravel kalau 'tahun' bukan auto-increment (AI)
    public $incrementing = false;

    protected $fillable = ['tahun', 'granularitas', 'metrik_tersedia'];

    protected $casts = [
        'metrik_tersedia' => 'array',
    ];
}