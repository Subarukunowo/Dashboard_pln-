<?php

namespace App\Http\Requests\Pendapatan;

use Illuminate\Foundation\Http\FormRequest;

class StoreRtFactPendapatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tahun'            => ['required', 'integer', 'min:2000', 'max:2100'],
            'bulan'            => ['required', 'integer', 'min:1', 'max:12'],
            'is_triwulan'      => ['required', 'boolean'],
            'is_akumulasi'     => ['required', 'boolean'],
            'tipe'             => ['required', 'string', 'max:50'],
            'sub_golongan_kode'=> ['nullable', 'string', 'max:20'],
            'golongan_kode'    => ['required', 'string', 'max:20'],
            'nilai_rp'         => ['required', 'numeric', 'min:0'],
            'nilai_rp_lalu'    => ['nullable', 'numeric', 'min:0'],
            'pct_pencapaian'   => ['nullable', 'numeric'],
            'sumber'           => ['nullable', 'string', 'max:100'],
        ];
    }
}
