<?php

namespace App\Services\ETL;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class Extractor
{
    public function fromExcel(string $filePath): array
    {
        try {
            $sheets = Excel::toArray([], $filePath);
            return $sheets;
        } catch (\Exception $e) {
            Log::error("Extractor error: " . $e->getMessage(), ['file' => $filePath]);
            throw $e;
        }
    }

    public function fromCsv(string $filePath): array
    {
        $rows = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = $row;
            }
            fclose($handle);
        }
        return [$rows];
    }
}