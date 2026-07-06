<?php

namespace App\Http\Requests\Produksi;

use Illuminate\Foundation\Http\FormRequest;

class StoreRtFactProduksiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tahun'                 => 'required|integer',
            'bulan'                 => 'required|integer|min:1|max:12',
            'tipe_losses'           => 'required|in:dengan_emin,tanpa_emin',
            'prod_bruto'            => 'nullable|numeric',
            'ps_kit'                => 'nullable|numeric',
            'prod_netto'            => 'nullable|numeric',
            'loko_transmisi'        => 'nullable|numeric',
            'siap_salur_transmisi'  => 'nullable|numeric',
            'ps_gi'                 => 'nullable|numeric',
            'prod_siap_salur'       => 'nullable|numeric',
            'interkoneksi'          => 'nullable|numeric',
            'loko_distribusi'       => 'nullable|numeric',
            'ps_distribusi'         => 'nullable|numeric',
            'blkg_padang'           => 'nullable|numeric',
            'sembulang'             => 'nullable|numeric',
            'buluh'                 => 'nullable|numeric',
            'p_nguan'               => 'nullable|numeric',
            'penjualan_tul3_09'     => 'nullable|numeric',
            'emin_sistem'           => 'nullable|numeric',
            'sumber'                => 'in:spreadsheet,manual',
        ];
    }
}