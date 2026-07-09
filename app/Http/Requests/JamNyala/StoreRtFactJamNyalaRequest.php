<?php

namespace App\Http\Requests\JamNyala;

use Illuminate\Foundation\Http\FormRequest;

class StoreRtFactJamNyalaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tahun'          => ['required', 'integer', 'min:2000', 'max:2100'],
            'bulan'          => ['required', 'integer', 'min:1', 'max:12'],
            'is_triwulan'    => ['required', 'boolean'],
            'is_akumulasi'   => ['required', 'boolean'],
            'tipe'           => ['required', 'string', 'max:50'],
            'golongan_kode'  => ['required', 'string', 'max:40'],
            'nilai_jam'      => ['required', 'numeric', 'min:0'],
            'pct_pencapaian' => ['nullable', 'numeric'],
            'sumber'         => ['nullable', 'string', 'max:100'],
        ];
    }
}
