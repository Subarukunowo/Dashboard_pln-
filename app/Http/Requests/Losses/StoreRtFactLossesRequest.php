<?php

namespace App\Http\Requests\Losses;

use Illuminate\Foundation\Http\FormRequest;

class StoreRtFactLossesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tahun'        => ['required', 'integer', 'min:2000', 'max:2100'],
            'bulan'        => ['required', 'integer', 'min:1', 'max:12'],
            'is_triwulan'  => ['required', 'boolean'],
            'is_akumulasi' => ['required', 'boolean'],
            'tipe_losses'  => ['required', 'string', 'max:50'],
            'tipe'         => ['required', 'string', 'max:50'],
           'kategori' => ['sometimes', 'required', 'in:transmisi,distribusi,jaringan'],
            'nilai_pct'    => ['nullable', 'numeric'],
            'nilai_kwh'    => ['nullable', 'numeric', 'min:0'],
            'sumber'       => ['nullable', 'string', 'max:100'],
        ];
    }
}
