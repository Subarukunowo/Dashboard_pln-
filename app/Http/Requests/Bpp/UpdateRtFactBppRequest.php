<?php

namespace App\Http\Requests\Bpp;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRtFactBppRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tahun'       => ['sometimes', 'integer', 'min:2000', 'max:2100'],
            'bulan'       => ['sometimes', 'integer', 'min:1', 'max:12'],
            'versi'       => ['sometimes', 'string', 'max:50'],
            'jenis_bpp'   => ['sometimes', 'string', 'max:50'],
            'nilai_rpkwh' => ['sometimes', 'numeric', 'min:0'],
            'sumber'      => ['nullable', 'string', 'max:100'],
        ];
    }
}
