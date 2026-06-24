<?php

namespace App\Http\Requests\Stats;

use Illuminate\Foundation\Http\FormRequest;

class StatsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tahun' => ['sometimes', 'integer', 'min:2000', 'max:2100'],
            'bulan' => ['sometimes', 'integer', 'min:1', 'max:12'],
        ];
    }

    public function tahun(): int
    {
        return (int) $this->input('tahun', now()->year);
    }

    public function bulan(): int
    {
        return (int) $this->input('bulan', now()->month);
    }
}