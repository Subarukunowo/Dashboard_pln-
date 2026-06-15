<?php

namespace App\Http\Requests\Pelanggan;

use Illuminate\Foundation\Http\FormRequest;

class StoreRtFactPelangganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tahun'             => 'required|integer',
            'bulan'             => 'required|integer|min:1|max:12',
            'is_triwulan'       => 'boolean',
            'is_akumulasi'      => 'boolean',
            'tipe'              => 'required|in:target,realisasi',
            'sub_golongan_kode' => 'required|string|max:30',
            'golongan_kode'     => 'required|string|max:10',
            'nilai_pelanggan'   => 'required|numeric',
            'nilai_kva'         => 'nullable|numeric',
            'pct_pencapaian'    => 'nullable|numeric',
            'sumber'            => 'in:spreadsheet,manual',
        ];
    }
}