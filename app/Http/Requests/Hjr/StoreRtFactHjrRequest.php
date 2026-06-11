<?php

namespace App\Http\Requests\Hjr;

use Illuminate\Foundation\Http\FormRequest;

class StoreRtFactHjrRequest extends FormRequest
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
            'tipe'         => ['required', 'string', 'max:50'],
            'tarif_kode'   => ['required', 'string', 'max:20'],
            'tegangan'     => ['nullable', 'string', 'max:20'],
            'nilai_rpkwh'  => ['required', 'numeric', 'min:0'],
            'sumber'       => ['nullable', 'string', 'max:100'],
        ];
    }
}
