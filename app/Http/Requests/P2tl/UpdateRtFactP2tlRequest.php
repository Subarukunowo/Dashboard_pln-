<?php

namespace App\Http\Requests\P2tl;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRtFactP2tlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tahun'            => ['sometimes', 'integer', 'min:2000', 'max:2100'],
            'bulan'            => ['sometimes', 'integer', 'min:1', 'max:12'],
            'is_triwulan'      => ['sometimes', 'boolean'],
            'is_akumulasi'     => ['sometimes', 'boolean'],
            'tipe'             => ['sometimes', 'string', 'max:50'],
            'jumlah_temuan'    => ['sometimes', 'integer', 'min:0'],
            'nilai_tagihan_rp' => ['sometimes', 'numeric', 'min:0'],
            'sumber'           => ['nullable', 'string', 'max:100'],
        ];
    }
}
