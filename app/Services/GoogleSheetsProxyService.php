<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class GoogleSheetsProxyService
{
    public function __construct(
        protected string $gasDataUrl = '',
        protected string $gasHistorisUrl = '',
        protected int $timeout = 60,
        protected int $cacheMinutes = 5,
        protected int $rateLimit = 60
    ) {
        $this->gasDataUrl = config('services.gas.data_url', '');
        $this->gasHistorisUrl = config('services.gas.historis_url', '');
        $this->timeout = config('services.pln.api_timeout', 60);
        $this->cacheMinutes = config('services.proxy.cache_minutes', 5);
        $this->rateLimit = config('services.proxy.rate_limit', 60);
    }

    public function fetch(string $type, array $payload, ?string $userIp = null): array
    {
        $url = $type === 'historis' ? $this->gasHistorisUrl : $this->gasDataUrl;

        // Fallback jika URL GAS belum diset
        if (empty($url) || !str_contains($url, 'script.google.com')) {
            return [
                'dummy' => true,
                'type' => $type,
                'payload' => $payload,
                'message' => 'Set GAS_DATA_URL di .env untuk aktifkan proxy real',
                'labels' => ['Jan','Feb','Mar','Apr','Mei','Jun'],
                'values' => [10, 25, 15, 30, 20, 35],
            ];
        }

        if ($userIp) {
            $executed = RateLimiter::attempt("gas-proxy:{$userIp}", $this->rateLimit, fn() => true);
            if (!$executed) {
                throw new \Exception('Too many requests. Try again later.');
            }
        }

        $cacheKey = "gas_proxy:{$type}:" . md5(json_encode($payload));
        
        return Cache::remember($cacheKey, now()->addMinutes($this->cacheMinutes), function () use ($url, $payload, $type) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'User-Agent' => 'PLN-Dashboard-Proxy/1.0',
                    ])
                    ->post($url, $payload);

                if ($response->successful()) {
                    Log::channel('daily')->info('GAS Proxy Success', ['type' => $type]);
                    return $response->json() ?? [];
                }

                throw new \Exception("GAS Error ({$response->status()}): " . $response->body());

            } catch (\Exception $e) {
                Log::error('GAS Proxy Exception', ['type' => $type, 'error' => $e->getMessage()]);
                throw $e;
            }
        });
    }
}