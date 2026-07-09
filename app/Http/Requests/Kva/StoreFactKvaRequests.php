<?php

namespace App\Http\Requests\Kva;

use Illuminate\Foundation\Http\FormRequest;

class StoreFactKvaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tahun'             => 'required|integer|min:2000|max:2100',
            'bulan'             => 'required|integer|min:1|max:12',
            'is_triwulan'       => 'sometimes|boolean',
            'is_akumulasi'      => 'sometimes|boolean',
            'tipe'              => 'required|in:target,realisasi',
            'sub_golongan_kode' => 'required|string|max:30',
            'golongan_kode'     => 'nullable|string|max:10',
            'nilai_kva'         => 'required|numeric',
            'sumber'            => 'sometimes|string|max:20',
        ];
    }
}