<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimGolongan extends Model
{
    protected $table      = 'dim_golongan';
    protected $primaryKey = 'kode';
    public    $incrementing = false;
    public    $timestamps   = false;
    protected $keyType    = 'string';

    protected $fillable = ['kode', 'nama', 'alias', 'urutan'];

    protected $casts = ['alias' => 'array'];

    public function facts()
    {
        return $this->hasMany(FactHistoris::class, 'golongan_kode', 'kode');
    }

    public static function resolveAlias(string $input): ?string
    {
        $lower = strtolower(trim($input));
        return static::all()->first(function ($g) use ($lower) {
            return in_array($lower, array_map('strtolower', $g->alias ?? []));
        })?->kode;
    }
}