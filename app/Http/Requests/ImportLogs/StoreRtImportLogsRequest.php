<?php

namespace App\Http\Requests\ImportLogs;

use Illuminate\Foundation\Http\FormRequest;

class StoreRtImportLogsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'filename'      => ['required', 'string', 'max:255'],
            'sheet_name'    => ['required', 'string', 'max:100'],
            'tahun'         => ['required', 'integer', 'min:2000', 'max:2100'],
            'bulan'         => ['required', 'integer', 'min:1', 'max:12'],
            'rows_inserted' => ['nullable', 'integer', 'min:0'],
            'rows_updated'  => ['nullable', 'integer', 'min:0'],
            'rows_skipped'  => ['nullable', 'integer', 'min:0'],
            'catatan'       => ['nullable', 'string'],
            'user_id'       => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
