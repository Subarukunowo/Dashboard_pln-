<?php

namespace App\Http\Requests\Pelanggan;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRtFactPelangganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tahun'            => ['sometimes', 'integer', 'min:2000', 'max:2100'],
            'bulan'            => ['sometimes', 'integer', 'min:1', 'max:12'],
            'is_triwulan'      => ['sometimes', 'boolean'],
            'is_akumulasi'     => ['sometimes', 'boolean'],
            'tipe'             => ['sometimes', 'string', 'max:50'],
            'sub_golongan_kode'=> ['nullable', 'string', 'max:20'],
            'golongan_kode'    => ['sometimes', 'string', 'max:20'],
            'nilai_pelanggan'  => ['sometimes', 'numeric', 'min:0'],
            'nilai_kva'        => ['nullable', 'numeric', 'min:0'],
            'pct_pencapaian'   => ['nullable', 'numeric'],
            'sumber'           => ['nullable', 'string', 'max:100'],
        ];
    }
}
