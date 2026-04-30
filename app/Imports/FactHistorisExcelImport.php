<?php

namespace App\Imports;

use App\Models\FactHistoris;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Facades\Log;

class FactHistorisExcelImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading, SkipsEmptyRows
{
    protected int $tahun;
    protected int $bulan;
    protected string $sumber;

    protected array $metrikMap = [
        'daya tersambung'               => 'daya_va',
        'delta gwh'                     => 'delta_gwh',
        'growth'                        => 'growth',
        'komposisi'                     => 'komposisi',
        'kwh jual'                      => 'kwh_jual',
        'mwh jual'                      => 'mwh_jual',
        'pelanggan'                     => 'pelanggan',
        'pertumbuhan yoy kumulatif'     => 'pertumbuhan_yoy',
        'rp/kwh jual'                   => 'rpkwh',
        'rupiah penjualan'              => 'rupiah',
    ];

    protected array $golonganMap = [
        'sosial'        => 'S',
        'rumah tangga'  => 'R',
        'bisnis'        => 'B',
        'industri'      => 'I',
        'publik'        => 'P',
        'multiguna'     => 'M',
    ];

    public function __construct(int $tahun, int $bulan, string $sumber = 'import')
    {
        $this->tahun  = $tahun;
        $this->bulan  = $bulan;
        $this->sumber = $sumber;
    }

    public function model(array $row)
    {
        if (empty($row['metrik']) || stripos($row['metrik'], 'metrik') !== false) {
            return null;
        }

        $metrikKode = $this->getMetrikKode($row['metrik']);
        if (!$metrikKode) {
            Log::warning("FactHistorisExcelImport: Metrik '{$row['metrik']}' tidak dikenali, baris diabaikan");
            return null;
        }

        $satuan = $this->cleanString($row['satuan'] ?? null);
        $models = [];

        foreach ($this->golonganMap as $excelKey => $golonganKode) {
            $value = $this->parseNumeric($row[$excelKey] ?? null);
            
            if ($value !== null) {
                $models[] = new FactHistoris([
                    'tahun'                 => $this->tahun,
                    'bulan'                 => $this->bulan,
                    'is_triwulan'           => 0,
                    'is_akumulasi'          => 0,
                    'is_jumlah_tahunan'     => 0,
                    'golongan_kode'         => $golonganKode,
                    'metrik_kode'           => $metrikKode,
                    'nilai'                 => $value,
                    'satuan'                => $satuan,
                    'sumber'                => $this->sumber,
                ]);
            }
        }

        return $models;
    }

    protected function getMetrikKode(string $namaMetrik): ?string
    {
        $key = strtolower(trim($namaMetrik));
        
        if (isset($this->metrikMap[$key])) {
            return $this->metrikMap[$key];
        }
        
        foreach ($this->metrikMap as $excelName => $dbCode) {
            if (stripos($key, $excelName) !== false) {
                return $dbCode;
            }
        }
        
        return null;
    }

    protected function parseNumeric($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $num = floatval($value);
            return is_finite($num) ? $num : null;
        }

        $value = trim($value);
        $value = str_replace(' ', '', $value);
        
        if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (strpos($value, ',') !== false) {
            $parts = explode(',', $value);
            $lastPart = end($parts);
            
            if (strlen($lastPart) === 3 && count($parts) > 2) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '.', $value);
            }
        }
        
        $num = floatval($value);
        return is_finite($num) ? $num : null;
    }

    protected function cleanString(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        return trim($value);
    }

    public function rules(): array
    {
        return [
            'metrik' => 'required|string',
            'satuan' => 'nullable|string',
            '*.numeric' => 'nullable',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'metrik.required' => 'Kolom Metrik wajib diisi',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}