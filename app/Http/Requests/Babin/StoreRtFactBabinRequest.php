<?php

namespace App\Http\Requests\Babin;

use Illuminate\Foundation\Http\FormRequest;

class StoreRtFactBabinRequest extends FormRequest
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
            'is_akumulasi' => ['required', 'boolean'],
            'area'         => ['required', 'string', 'max:100'],
            'tipe'         => ['required', 'string', 'max:50'],
            'nilai_kwh'    => ['required', 'numeric', 'min:0'],
            'nilai_rp'     => ['required', 'numeric', 'min:0'],
            'sumber'       => ['nullable', 'string', 'max:100'],
        ];
    }
}
