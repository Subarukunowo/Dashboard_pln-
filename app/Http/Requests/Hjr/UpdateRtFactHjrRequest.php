<?php

namespace App\Http\Requests\Hjr;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRtFactHjrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tahun'        => ['sometimes', 'integer', 'min:2000', 'max:2100'],
            'bulan'        => ['sometimes', 'integer', 'min:1', 'max:12'],
            'is_triwulan'  => ['sometimes', 'boolean'],
            'is_akumulasi' => ['sometimes', 'boolean'],
            'tipe'         => ['sometimes', 'string', 'max:50'],
            'tarif_kode'   => ['sometimes', 'string', 'max:20'],
            'tegangan'     => ['nullable', 'string', 'max:20'],
            'nilai_rpkwh'  => ['sometimes', 'numeric', 'min:0'],
            'sumber'       => ['nullable', 'string', 'max:100'],
        ];
    }
}
