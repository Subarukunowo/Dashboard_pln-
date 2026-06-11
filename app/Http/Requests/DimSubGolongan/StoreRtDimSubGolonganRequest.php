<?php

namespace App\Http\Requests\DimSubGolongan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRtDimSubGolonganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode'          => ['required', 'string', 'max:20', 'unique:rt_dim_sub_golongan,kode'],
            'nama'          => ['required', 'string', 'max:100'],
            'golongan_kode' => ['required', 'string', 'max:20'],
            'urutan'        => ['nullable', 'integer', 'min:0'],
        ];
    }
}
