<?php

namespace App\Http\Requests\P2tl;

use Illuminate\Foundation\Http\FormRequest;

class StoreRtFactP2tlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tahun'            => ['required', 'integer', 'min:2000', 'max:2100'],
            'bulan'            => ['required', 'integer', 'min:1', 'max:12'],
            'is_triwulan'      => ['required', 'boolean'],
            'is_akumulasi'     => ['required', 'boolean'],
            'tipe'             => ['required', 'string', 'max:50'],
            'jumlah_temuan'    => ['required', 'integer', 'min:0'],
            'nilai_tagihan_rp' => ['required', 'numeric', 'min:0'],
            'sumber'           => ['nullable', 'string', 'max:100'],
        ];
    }
}
