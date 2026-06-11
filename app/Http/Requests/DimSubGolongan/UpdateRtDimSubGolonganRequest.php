<?php

namespace App\Http\Requests\DimSubGolongan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRtDimSubGolonganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode'          => ['sometimes', 'string', 'max:20', Rule::unique('rt_dim_sub_golongan', 'kode')->ignore($this->route('kode'), 'kode')],
            'nama'          => ['sometimes', 'string', 'max:100'],
            'golongan_kode' => ['sometimes', 'string', 'max:20'],
            'urutan'        => ['nullable', 'integer', 'min:0'],
        ];
    }
}
