<?php

namespace App\Http\Requests\Losses;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRtFactLossesRequest extends FormRequest
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
            'tipe_losses'  => ['sometimes', 'string', 'max:50'],
            'tipe'         => ['sometimes', 'string', 'max:50'],
            'kategori'     => ['sometimes', 'in:transmisi,distribusi,jaringan'],
            'nilai_pct'    => ['nullable', 'numeric'],
            // FIX: min:0 dihapus, sama alasan seperti Store
            'nilai_kwh'    => ['nullable', 'numeric'],
            'sumber'       => ['nullable', 'string', 'max:100'],
        ];
    }
}