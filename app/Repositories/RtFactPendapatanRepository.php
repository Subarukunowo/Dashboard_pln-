<?php

namespace App\Repositories;

use App\Models\RtFactPendapatan;

class RtFactPendapatanRepository extends BaseRepository
{
    public function __construct(RtFactPendapatan $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters($query, array $filters)
    {
        // Helper: pakai whereIn kalau value berupa array (mis. tahun => [2026, 2025]),
        // pakai where biasa kalau scalar. Sebelumnya where() dipanggil langsung dengan
        // array sebagai value — itu bikin query salah/kosong diam-diam (root cause
        // "data tahun lalu tidak muncul padahal ada di database").
        $applyWhere = fn ($q, $column, $value) => is_array($value)
            ? $q->whereIn($column, $value)
            : $q->where($column, $value);

        return $query
            ->when(isset($filters['tahun']),            fn($q) => $applyWhere($q, 'tahun', $filters['tahun']))
            ->when(isset($filters['bulan']),             fn($q) => $applyWhere($q, 'bulan', $filters['bulan']))
            ->when(isset($filters['tipe']),              fn($q) => $applyWhere($q, 'tipe', $filters['tipe']))
            ->when(isset($filters['golongan_kode']),     fn($q) => $applyWhere($q, 'golongan_kode', $filters['golongan_kode']))
            ->when(isset($filters['sub_golongan_kode']), fn($q) => $applyWhere($q, 'sub_golongan_kode', $filters['sub_golongan_kode']))
            ->when(isset($filters['is_triwulan']),       fn($q) => $applyWhere($q, 'is_triwulan', $filters['is_triwulan']))
            ->when(isset($filters['is_akumulasi']),      fn($q) => $applyWhere($q, 'is_akumulasi', $filters['is_akumulasi']))
            ->when(isset($filters['sumber']),            fn($q) => $applyWhere($q, 'sumber', $filters['sumber']));
    }
}