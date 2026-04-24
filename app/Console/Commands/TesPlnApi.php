
<?php
// app/Console/Commands/TestPlnApi.php
/*
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestPlnApi extends Command
{
    protected $signature = 'pln:test-api {--url= : Optional custom URL}';
    protected $description = 'Test connection to PLN Google Apps Script API';

    public function handle(): int
    {
        $url = $this->option('url') ?? config('services.pln.historis_api_url');
        
        if (!$url) {
            $this->error('API URL not configured');
            return self::FAILURE;
        }

        $this->info("Testing: {$url}");
        
        try {
            $start = microtime(true);
            $response = Http::timeout(30)->get($url);
            $duration = round(microtime(true) - $start, 2);
            
            $this->line("Status: {$response->status()}");
            $this->line("Time: {$duration}s");
            $this->line("Size: " . round(strlen($response->body()) / 1024, 1) . " KB");
            
            if ($response->successful()) {
                $data = $response->json();
                $years = is_array($data) ? array_keys($data) : [];
                $this->info("Years available: " . implode(', ', $years));
                return self::SUCCESS;
            } else {
                $this->error("Response body preview: " . substr($response->body(), 0, 300));
                return self::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("Exception: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
*/