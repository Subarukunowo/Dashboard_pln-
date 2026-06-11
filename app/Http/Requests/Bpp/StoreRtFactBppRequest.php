<?php

namespace App\Http\Requests\Bpp;

use Illuminate\Foundation\Http\FormRequest;

class StoreRtFactBppRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tahun'       => ['required', 'integer', 'min:2000', 'max:2100'],
            'bulan'       => ['required', 'integer', 'min:1', 'max:12'],
            'versi'       => ['required', 'string', 'max:50'],
            'jenis_bpp'   => ['required', 'string', 'max:50'],
            'nilai_rpkwh' => ['required', 'numeric', 'min:0'],
            'sumber'      => ['nullable', 'string', 'max:100'],
        ];
    }
}
