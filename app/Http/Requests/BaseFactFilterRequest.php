<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseFactFilterRequest extends FormRequest
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
            'tipe'         => ['sometimes', 'string', 'max:50'],
            'sumber'       => ['sometimes', 'string', 'max:100'],
            'is_triwulan'  => ['sometimes', 'boolean'],
            'is_akumulasi' => ['sometimes', 'boolean'],
            'per_page'     => ['sometimes', 'integer', 'min:1', 'max:200'],
        ];
    }

    public function filters(): array
    {
        return $this->only(array_keys($this->rules()));
    }
}
