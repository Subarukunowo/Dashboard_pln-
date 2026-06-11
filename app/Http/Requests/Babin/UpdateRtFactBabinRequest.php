<?php

namespace App\Http\Requests\Babin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRtFactBabinRequest extends FormRequest
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
            'is_akumulasi' => ['sometimes', 'boolean'],
            'area'         => ['sometimes', 'string', 'max:100'],
            'tipe'         => ['sometimes', 'string', 'max:50'],
            'nilai_kwh'    => ['sometimes', 'numeric', 'min:0'],
            'nilai_rp'     => ['sometimes', 'numeric', 'min:0'],
            'sumber'       => ['nullable', 'string', 'max:100'],
        ];
    }
}
