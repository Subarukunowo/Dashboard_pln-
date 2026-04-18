<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DebugPlnStructure extends Command
{
    protected $signature = 'pln:debug-structure {tahun=1997 : Year to inspect} {--limit=2 : Rows to show per category}';
    protected $description = 'Inspect raw JSON structure from PLN API for debugging';

    public function handle(): int
    {
        $tahun = $this->argument('tahun');
        $limit = (int) $this->option('limit');
        $apiUrl = config('services.pln.historis_api_url');
        
        if (!$apiUrl) {
            $this->error('API URL not configured');
            return self::FAILURE;
        }

        $this->info("Fetching data for year: {$tahun}");
        
        try {
            $response = Http::timeout(60)->get($apiUrl);
            
            if (!$response->successful()) {
                $this->error("HTTP {$response->status()}");
                return self::FAILURE;
            }
            
            $allData = $response->json();
            $yearData = $allData[$tahun] ?? $allData[(string)$tahun] ?? null;
            
            if (!$yearData) {
                $this->error("No data found for year {$tahun}");
                $this->line("Available years: " . implode(', ', array_keys($allData)));
                return self::FAILURE;
            }
            
            $this->info("Year {$tahun} structure:");
            $this->info(str_repeat('-', 60));
            $this->line("Raw type: " . gettype($yearData));
            
            // Handle jika yearData adalah array numerik (list of categories)
            if (is_array($yearData) && array_is_list($yearData)) {
                $this->info("Format: Array of category objects");
                foreach ($yearData as $idx => $categoryItem) {
                    $this->newLine();
                    $this->info("ITEM [{$idx}]:");
                    $this->inspectItem($categoryItem, $limit);
                }
            } 
            // Handle jika yearData adalah object/map (kategori => data)
            elseif (is_array($yearData)) {
                $this->info("Format: Map of categories");
                foreach ($yearData as $kategori => $rows) {
                    $this->newLine();
                    $this->info("KATEGORI: [{$kategori}]");
                    $this->inspectRows($rows, $limit);
                }
            }
            // Handle jika bukan array
            else {
                $this->warn("Unexpected type: " . gettype($yearData));
                $this->line("Raw preview: " . json_encode($yearData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            }
            
            // Save full JSON untuk inspeksi manual
            $debugFile = storage_path("logs/pln_debug_{$tahun}.json");
            file_put_contents($debugFile, json_encode($yearData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->newLine();
            $this->info("Full JSON saved to: {$debugFile}");
            
            return self::SUCCESS;
            
        } catch (\Throwable $e) {
            $this->error("Exception: " . $e->getMessage());
            Log::error('PLN Debug Exception', [
                'error' => $e->getMessage(), 
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return self::FAILURE;
        }
    }
    
    private function inspectItem($item, int $limit): void
    {
        if (!is_array($item) && !is_object($item)) {
            $this->line("  Value: " . json_encode($item));
            return;
        }
        
        // Konversi object ke array jika perlu
        $arr = is_object($item) ? (array) $item : $item;
        
        $this->line("  Keys: " . implode(', ', array_keys($arr)));
        
        // Cek apakah ini punya key 'category' dan 'data'
        if (isset($arr['category']) && isset($arr['data'])) {
            $this->line("  Category name: {$arr['category']}");
            $this->inspectRows($arr['data'], $limit);
        } else {
            // Tampilkan sebagai rows biasa
            $this->inspectRows($arr, $limit);
        }
    }
    
    private function inspectRows($rows, int $limit): void
    {
        if (!is_array($rows)) {
            $this->line("  Not an array: " . gettype($rows));
            return;
        }
        
        $this->line("  Type: " . gettype($rows));
        $this->line("  Count: " . count($rows));
        
        if (count($rows) === 0) {
            $this->line("  (empty)");
            return;
        }
        
        // Ambil first row dengan aman
        $firstRow = reset($rows);
        
        if (is_array($firstRow) || is_object($firstRow)) {
            $rowArr = is_object($firstRow) ? (array) $firstRow : $firstRow;
            $this->line("  First row keys:");
            foreach (array_keys($rowArr) as $key) {
                $sample = $rowArr[$key];
                $type = gettype($sample);
                $value = is_string($sample) ? "\"{$sample}\"" : (is_null($sample) ? 'null' : $sample);
                $this->line("    - {$key}: {$value} ({$type})");
            }
        } else {
            $this->line("  First row is scalar: " . gettype($firstRow) . " = {$firstRow}");
        }
        
        // Show sample rows
        $this->line("  Sample rows (max {$limit}):");
        $count = 0;
        foreach ($rows as $idx => $row) {
            if ($count >= $limit) break;
            $rowArr = is_object($row) ? (array) $row : $row;
            $this->line("    [{$idx}] " . json_encode($rowArr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $count++;
        }
    }
}

// Polyfill untuk PHP < 8.1
if (!function_exists('array_is_list')) {
    function array_is_list(array $array): bool {
        if ($array === []) return true;
        return array_keys($array) === range(0, count($array) - 1);
    }
}