<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ETL\Extractor;
use App\Services\ETL\Transformer;
use App\Services\ETL\Loader;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ImportHistoris extends Command
{
    protected $signature = 'etl:import-historis 
        {--tahun= : Import specific year only} 
        {--start=1997 : Start year for range import} 
        {--end=2026 : End year for range import}
        {--dry-run : Preview transformation without saving to DB}
        {--force : Skip confirmation for bulk import}
        {--chunk=500 : Batch size for database upsert}
        {--no-cache : Skip cache and fetch fresh from API}';
        
    protected $description = 'Import historical PLN data via ETL pipeline (1997-2026)';

    private array $summary = [
        'processed' => 0,
        'inserted' => 0,
        'failed' => 0,
        'skipped' => 0,
        'errors' => [],
    ];

    public function handle(
        Extractor $extractor, 
        Transformer $transformer, 
        Loader $loader
    ): int {
        $this->validateOptions();
        
        $dryRun = $this->option('dry-run');
        $chunkSize = (int) $this->option('chunk');
        $noCache = $this->option('no-cache');
        $range = $this->resolveYearRange();
        
        if (!$this->confirmBulkOperation($range, $dryRun)) {
            $this->info('Import cancelled by user.');
            return self::SUCCESS;
        }

        $this->renderHeader($range, $dryRun, $chunkSize);
        
        // Fetch data sekali saja untuk semua tahun
        $allData = $this->fetchAllData($extractor, $noCache);
        
        if (empty($allData)) {
            $this->error('Failed to fetch data from API. Check logs for details.');
            return self::FAILURE;
        }

        $this->processYearRange($range, $allData, $transformer, $loader, $dryRun, $chunkSize);
        
        $this->renderSummary();
        
        return $this->summary['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function validateOptions(): void
    {
        $start = (int) $this->option('start');
        $end = (int) $this->option('end');
        $tahun = $this->option('tahun');
        $chunk = (int) $this->option('chunk'); // FIX: Ambil value chunk langsung

        if ($tahun !== null && ($start !== 1997 || $end !== 2026)) {
            $this->warn('Option --tahun overrides --start/--end');
        }

        if ($start > $end) {
            $this->error('Start year cannot be greater than end year');
            exit(1);
        }

        // Validasi chunk size
        if ($chunk < 100 || $chunk > 5000) {
            $this->warn('Chunk size should be between 100-5000. Using default 500.');
        }
    }

    protected function resolveYearRange(): array
    {
        $single = $this->option('tahun');
        
        if ($single !== null) {
            $year = (int) $single;
            return ['start' => $year, 'end' => $year, 'is_single' => true];
        }

        return [
            'start' => (int) $this->option('start'),
            'end' => (int) $this->option('end'),
            'is_single' => false,
        ];
    }

    protected function confirmBulkOperation(array $range, bool $dryRun): bool
    {
        if ($dryRun || $range['is_single']) {
            return true;
        }

        $totalYears = $range['end'] - $range['start'] + 1;
        
        if ($totalYears > 10 && !$this->option('force')) {
            return $this->confirm(
                "You are about to process {$totalYears} years ({$range['start']}-{$range['end']}).\n" .
                "This may take several minutes. Continue?",
                false
            );
        }

        return true;
    }

    protected function renderHeader(array $range, bool $dryRun, int $chunkSize): void
    {
        $mode = $dryRun ? 'DRY RUN' : 'LIVE';
        $rangeLabel = $range['is_single'] 
            ? "Year: {$range['start']}" 
            : "Range: {$range['start']} - {$range['end']}";

        $this->newLine();
        $this->info('PLN Historis Data Importer');
        $this->info(str_repeat('=', 50));
        $this->line("Mode:   {$mode}");
        $this->line("{$rangeLabel}");
        $this->line("Chunk:  {$chunkSize} records/batch");
        $this->line('Cache:  ' . ($this->option('no-cache') ? 'Disabled (fresh fetch)' : 'Enabled (24h)'));
        $this->info(str_repeat('=', 50));
        $this->newLine();
    }

 protected function fetchAllData(Extractor $extractor, bool $noCache): array
{
    $apiUrl = config('services.pln.historis_api_url');
    
    if (!$apiUrl) {
        $this->error('API URL not configured. Check config/services.php or .env');
        Log::error('PLN Import: API URL is empty in configuration');
        return [];
    }

    $timeout = config('services.pln.api_timeout', 120);
    $cacheKey = 'pln_historis_raw_data_v2';
    
    if ($noCache) {
        Cache::forget($cacheKey);
        $this->info('Cache bypassed. Fetching fresh data...');
    }

    return Cache::remember($cacheKey, 60 * 24, function () use ($apiUrl, $timeout) {
        $this->info("Fetching from: {$apiUrl}");
        
        try {
            // Gunakan Http facade langsung di sini untuk kontrol error yang lebih baik
            $response = \Illuminate\Support\Facades\Http::timeout($timeout)->get($apiUrl);
            
            if (!$response->successful()) {
                $msg = "API returned status {$response->status()}";
                $this->error($msg);
                Log::error('PLN API HTTP Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'url' => $apiUrl,
                ]);
                return [];
            }
            
            $data = $response->json();
            
            if ($data === null) {
                $msg = "API returned invalid JSON";
                $this->error($msg);
                Log::error('PLN API JSON Parse Error', [
                    'body_preview' => substr($response->body(), 0, 200),
                ]);
                return [];
            }
            
            $yearCount = is_array($data) ? count($data) : 0;
            $this->info("Data fetched: {$yearCount} years found");
            Log::info('PLN API fetch successful', ['years_count' => $yearCount]);
            
            return $data;
            
        } catch (\Throwable $e) {
            $msg = "Fetch exception: " . $e->getMessage();
            $this->error($msg);
            Log::error('PLN API Fetch Exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'url' => $apiUrl,
            ]);
            return [];
        }
    });
}
    protected function processYearRange(
        array $range,
        array $allData,
        Transformer $transformer,
        Loader $loader,
        bool $dryRun,
        int $chunkSize
    ): void {
        $totalYears = $range['end'] - $range['start'] + 1;
        
        $progress = $this->output->createProgressBar($totalYears);
        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s% %message%');
        $progress->start();

        for ($tahun = $range['start']; $tahun <= $range['end']; $tahun++) {
            $progress->setMessage("[{$tahun}]");
            
            $result = $this->processSingleYear(
                $tahun,
                $allData,
                $transformer,
                $loader,
                $dryRun,
                $chunkSize
            );

            $this->summary['processed']++;
            $this->summary['inserted'] += $result['inserted'];
            $this->summary['failed'] += $result['failed'];
            $this->summary['skipped'] += $result['skipped'];
            
            if (!empty($result['error'])) {
                $this->summary['errors'][$tahun] = $result['error'];
            }

            $progress->advance();
        }

        $progress->finish();
        $this->newLine(2);
    }

    protected function processSingleYear(
        int $tahun,
        array $allData,
        Transformer $transformer,
        Loader $loader,
        bool $dryRun,
        int $chunkSize
    ): array {
        $result = ['inserted' => 0, 'failed' => 0, 'skipped' => 0];

        try {
            $yearData = $allData[(string) $tahun] ?? null;

            if (!$yearData || empty($yearData)) {
                $this->warn("   [{$tahun}] No data available");
                $result['skipped']++;
                return $result;
            }

            $records = $transformer->transform($yearData, $tahun);

            if ($dryRun) {
                $this->info("   [{$tahun}] Transformed: " . count($records) . " records");
                return ['inserted' => count($records), 'failed' => 0, 'skipped' => 0];
            }

            if (empty($records)) {
                $this->warn("   [{$tahun}] No valid records after transformation");
                $result['skipped']++;
                $this->logDebugSample($yearData, $tahun);
                return $result;
            }

            $loader->setChunkSize($chunkSize);
            $loadResult = $loader->load($records, $tahun);
            
            $result['inserted'] = $loadResult['inserted'] ?? 0;
            $this->info("   [{$tahun}] Saved: {$result['inserted']} records");

        } catch (\Throwable $e) {
            $errorMsg = $e->getMessage();
            $this->error("   [{$tahun}] Error: {$errorMsg}");
            $result['failed']++;
            $result['error'] = $errorMsg;
            
            Log::error("ETL Import failed for year {$tahun}", [
                'error' => $errorMsg,
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        return $result;
    }

    protected function logDebugSample(array $rawData, int $tahun): void
    {
        if (!config('app.debug')) {
            return;
        }

        $categories = array_keys($rawData);
        $sample = [];
        
        foreach (array_slice($categories, 0, 2) as $cat) {
            $sample[$cat] = array_slice($rawData[$cat], 0, 1);
        }

        Log::debug("ETL Debug Sample for year {$tahun}", [
            'categories' => $categories,
            'sample' => $sample,
        ]);
    }

    protected function renderSummary(): void
    {
        $this->newLine();
        $this->info('Import Summary');
        $this->info(str_repeat('-', 40));
        $this->line("Processed: {$this->summary['processed']} years");
        $this->line("Inserted:  {$this->summary['inserted']} records");
        $this->line("Skipped:   {$this->summary['skipped']} years");
        $this->line("Failed:    {$this->summary['failed']} years");
        
        if (!empty($this->summary['errors'])) {
            $this->newLine();
            $this->warn('Errors encountered:');
            foreach ($this->summary['errors'] as $year => $error) {
                $this->line("  - Year {$year}: {$error}");
            }
            $this->warn('Check logs: storage/logs/laravel.log');
        }
        
        $this->newLine();
    }
}