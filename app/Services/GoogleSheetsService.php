<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GoogleSheetsService
{
    protected $url;

    public function __construct()
    {
        $this->url = config('services.gsheets.url');
    }

    public function fetch()
    {
        $response = Http::get($this->url);

        if (!$response->successful()) {
            throw new \Exception("Gagal mengambil data");
        }

        return $response->json();
    }

    public function getSheet($name)
    {
        $data = $this->fetch();
        return $data[$name] ?? [];
    }

    public function extractMonths($row)
    {
        return collect($row)
            ->slice(6,6)
            ->map(fn($v) => (float)$v)
            ->values();
    }
}