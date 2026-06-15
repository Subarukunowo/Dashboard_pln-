<?php

namespace App\Http\Requests\Tunggakan;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRtFactTunggakanRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'tahun'               => 'sometimes|integer',
            'bulan'               => 'sometimes|integer|min:1|max:12',
            'pal_berjalan'        => 'nullable|numeric',
            'pal_tunggakan'       => 'nullable|numeric',
            'ts_berjalan'         => 'nullable|numeric',
            'ts_tunggakan'        => 'nullable|numeric',
            'jumlah_tunggakan'    => 'nullable|numeric',
            'pendapatan'          => 'nullable|numeric',
            'ratio_tunggakan_pct' => 'nullable|numeric',
            'sumber'              => 'in:spreadsheet,manual',
        ];
    }
}