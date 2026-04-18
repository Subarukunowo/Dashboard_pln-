<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ETL\Extractor;
use Illuminate\Support\Facades\Log;

class DebugExtractorOutput extends Command
{
    protected $signature = 'pln:debug-extractor 
        {tahun? : Year to debug (e.g., 2010)} 
        {--all : Debug all years 2005-2026}
        {--limit=2 : Max rows to show per category}
        {--raw : Show full JSON without formatting}';
        
    protected $description = 'Debug the normalized output from Extractor for specific years';

    public function handle(Extractor $extractor): int
    {
        $apiUrl = config('services.pln.historis_api_url');
        
        if (!$apiUrl) {
            $this->error('API URL not configured in config/services.php');
            return self::FAILURE;
        }

        $years = $this->option('all') 
            ? range(2005, 2026) 
            : [(int) ($this->argument('tahun') ?? 2010)];

        $limit = (int) $this->option('limit');
        $showRaw = $this->option('raw');

        $this->info("🔍 Debug Extractor Output");
        $this->line("Years: " . implode(', ', $years));
        $this->line("Limit per category: {$limit}");
        $this->newLine();

        foreach ($years as $tahun) {
            $this->info("═══════════════════════════════════════");
            $this->info("📅 YEAR: {$tahun}");
            $this->info("═══════════════════════════════════════");
            
            try {
                // Fetch via Extractor (dengan bypass cache)
                $data = $extractor->fromUrl($apiUrl, $tahun, [
                    'timeout' => 120, 
                    'raw' => true,
                    'no_cache' => true
                ]);
                
                if (empty($data)) {
                    $this->warn("   ⚠️  Extractor returned EMPTY data");
                    $this->newLine();
                    continue;
                }

                $this->line("✅ Data received. Type: " . gettype($data));
                
                if ($showRaw) {
                    // Show raw JSON
                    $this->line("\n📦 RAW JSON (first 2000 chars):");
                    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    $this->line(substr($json, 0, 2000) . (strlen($json) > 2000 ? '...' : ''));
                } else {
                    // Show structured preview
                    $this->line("📦 Categories found: " . count($data));
                    
                    foreach ($data as $kategori => $rows) {
                        $this->newLine();
                        $this->line("   📊 [{$kategori}]");
                        $this->line("      Rows: " . (is_array($rows) ? count($rows) : 'N/A'));
                        
                        if (is_array($rows) && count($rows) > 0) {
                            $this->line("      Sample keys (first row):");
                            $firstRow = $rows[0];
                            if (is_array($firstRow)) {
                                $keys = array_slice(array_keys($firstRow), 0, 8);
                                $this->line("         " . implode(', ', $keys));
                                
                                // Show sample values
                                $this->line("      Sample values:");
                                foreach (array_slice($firstRow, 0, $limit) as $k => $v) {
                                    $val = is_string($v) && strlen($v) > 40 ? substr($v, 0, 37) . '...' : $v;
                                    $val = $val === null ? 'null' : $val;
                                    $this->line("         • {$k}: {$val}");
                                }
                            }
                        }
                    }
                }
                
                // Save to file for deeper inspection
                $filename = "extractor_debug_{$tahun}.json";
                $filepath = storage_path("logs/{$filename}");
                file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $this->newLine();
                $this->line("💾 Full output saved to: storage/logs/{$filename}");
                
            } catch (\Throwable $e) {
                $this->error("   ❌ Error: " . $e->getMessage());
                Log::error('Extractor Debug Error', [
                    'year' => $tahun,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            
            $this->newLine();
        }

        $this->info("✅ Debug complete.");
        return self::SUCCESS;
    }
}