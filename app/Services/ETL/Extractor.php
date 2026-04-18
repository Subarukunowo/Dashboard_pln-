<?php

namespace App\Services\ETL;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Extractor
{
    public function fromUrl(string $url, int $tahun, array $options = []): array
    {
        try {
            $timeout = $options['timeout'] ?? 120;
            $cacheKey = 'pln_historis_raw_data_v10'; // Bump version for cache invalidation
            
            $allData = Cache::remember($cacheKey, 60 * 24, function () use ($url, $timeout) {
                Log::info('Fetching PLN historical data from API', ['url' => $url]);
                
                $response = Http::timeout($timeout)->get($url);
                
                if (!$response->successful()) {
                    Log::error('PLN API HTTP Error', [
                        'status' => $response->status(),
                        'body' => substr($response->body(), 0, 500),
                    ]);
                    return [];
                }
                
                $json = $response->json();
                if ($json === null) {
                    Log::error('PLN API returned invalid JSON');
                    return [];
                }
                
                return $json;
            });

            $tahunStr = (string) $tahun;
            $yearData = $allData[$tahunStr] ?? [];
            
            // Route to era-specific normalizer
            if ($tahun >= 2005 && $tahun <= 2019) {
                return $this->normalizeEra2005_2019($yearData, $tahun);
            } elseif ($tahun >= 1997 && $tahun <= 2004) {
                return $this->normalizeEra1997_2004($yearData);
            }
            
            // 2020+: return as-is (format already clean)
            return $yearData;

        } catch (\Exception $e) {
            Log::error('Extractor exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'tahun' => $tahun,
            ]);
            return [];
        }
    }

    /**
     * Normalisasi key bulan: handle semua variasi format
     * - "Jan" → "JAN-05" (with target year suffix)
     * - "Jan-16" → "JAN-16"
     * - "DES_2016" → "DES-16"
     * - "Des-15" → "DES-15"
     */
    private function normalizeMonthKey(string $key, int $targetYear): ?string
    {
        $keyUpper = strtoupper(trim($key));
        $months = [
            'JAN'=>'JAN', 'FEB'=>'FEB', 'MAR'=>'MAR', 'APR'=>'APR', 'MAY'=>'MAY', 'JUN'=>'JUN',
            'JUL'=>'JUL', 'AUG'=>'AUG', 'SEP'=>'SEP', 'OCT'=>'OCT', 'NOV'=>'NOV', 'DEC'=>'DEC',
            'MEI'=>'MEI', 'AGU'=>'AGU', 'OKT'=>'OKT', 'DES'=>'DES',
        ];
        
        // 1. Plain month name: "Jan", "FEB" → add target year suffix
        if (isset($months[$keyUpper])) {
            $suffix = str_pad($targetYear % 100, 2, '0', STR_PAD_LEFT);
            return "{$months[$keyUpper]}-{$suffix}";
        }
        
        // 2. Hyphen + 2-digit year: "Jan-16", "DES-15"
        if (preg_match('/^(JAN|FEB|MAR|APR|MEI|JUN|JUL|AGU|SEP|OKT|NOV|DES)-(\d{2})$/i', $keyUpper, $m)) {
            return "{$m[1]}-{$m[2]}";
        }
        
        // 3. Underscore + 4-digit year: "DES_2016" → "DES-16"
        if (preg_match('/^(JAN|FEB|MAR|APR|MEI|JUN|JUL|AGU|SEP|OKT|NOV|DES)_(\d{4})$/i', $keyUpper, $m)) {
            $yearSuffix = substr($m[2], -2);
            return "{$m[1]}-{$yearSuffix}";
        }
        
        return null;
    }

    /**
     * Detect & filter noise values: -1, #DIV/0!, null, empty strings
     */
    private function isNoiseValue($val): bool
    {
        if ($val === null || $val === '' || $val === '-' || $val === 'NULL') return true;
        if (is_string($val)) {
            $trimmed = strtoupper(trim($val));
            if (in_array($trimmed, ['#DIV/0!', '#N/A', '#REF!', '#VALUE!', 'NULL', ''])) return true;
        }
        // Placeholder numeric values
        if (is_numeric($val) && in_array((float) $val, [-1, -999, -9999], true)) return true;
        return false;
    }

    /**
     * Normalisasi era 2005-2019 dengan filtering noise & key normalization
     */
    private function normalizeEra2005_2019(array $rows, int $targetYear): array
    {
        $grouped = [];
        $currentCategory = null;
        $categoryKeywords = [
            'kwh jual', 'rupiah penjualan', 'pelanggan akumulasi', 
            'daya tersambung', 'rp/kwh jual', '% pertumbuhan', 'mwh jual'
        ];

        foreach ($rows as $row) {
            if (!is_array($row)) continue;
            
            $kategoriValue = $row['KEL. TARIF'] ?? $row['KEL_TARIF'] ?? $row['TARIF'] ?? '';
            $kategoriLower = strtolower(trim((string) $kategoriValue));

            // 1. Detect CATEGORY TITLE rows (e.g., "KWH JUAL")
            $isCategoryTitle = false;
            foreach ($categoryKeywords as $kw) {
                if (str_contains($kategoriLower, $kw)) {
                    $hasData = false;
                    foreach ($row as $k => $v) {
                        if (!in_array(strtoupper($k), ['KEL. TARIF', 'KEL_TARIF', 'TARIF']) && 
                            $v !== null && $v !== '' && $v !== 0 && $v !== '0') {
                            $hasData = true; break;
                        }
                    }
                    if (!$hasData) { $isCategoryTitle = true; break; }
                }
            }
            
            if ($isCategoryTitle) {
                $currentCategory = strtoupper(trim($kategoriValue));
                continue;
            }

            // 2. Skip HEADER rows
            if ($kategoriLower === 'kel. tarif' || $kategoriLower === 'kel_tarif') continue;
            if ($this->containsIsoDate($row)) continue;
            if (!$currentCategory) continue;

            // 3. Skip NOISE rows (growth rates, placeholders)
            if ($this->isNoiseRow($row)) continue;

            // 4. Build normalized row
            $normalizedRow = ['KEL_TARIF' => $kategoriValue];
            
            foreach ($row as $key => $val) {
                if (in_array(strtoupper($key), ['KEL. TARIF', 'KEL_TARIF', 'TARIF'])) continue;
                
                $keyUpper = strtoupper(trim($key));
                
                // ✅ Normalize month keys (handle all formats)
                $normalizedMonthKey = $this->normalizeMonthKey($key, $targetYear);
                if ($normalizedMonthKey) {
                    if (!$this->isNoiseValue($val)) {
                        $normalizedRow[$normalizedMonthKey] = $val;
                    }
                    continue;
                }
                
                // Handle special columns: RKAP_2016, MUTASI_2016, etc.
                if (preg_match('/^(RKAP|MUTASI|PENCAPAIAN|RATA-RATA)\s*[(_\-\s]?\s*(\d{4})?$/i', $key, $matches)) {
                    $colName = strtoupper($matches[1]);
                    $yearSuffix = $matches[2] ?? $targetYear;
                    if (!$this->isNoiseValue($val)) {
                        $normalizedRow["{$colName}_{$yearSuffix}"] = $val;
                    }
                    continue;
                }
                
                // Handle JUMLAH/TOTAL
                if (in_array($keyUpper, ['JUMLAH', 'TOTAL'])) {
                    if (!$this->isNoiseValue($val)) {
                        $normalizedRow['JUMLAH'] = $val;
                    }
                    continue;
                }
                
                // Skip ISO date strings
                if (is_string($val) && preg_match('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $val)) continue;
                
                // Skip numeric timestamp keys
                if (is_numeric($key) && $key > 1000000000) continue;
                
                // Default: normalize key & store if not noise
                $normalizedKey = strtoupper(str_replace([' ', '.', '-'], '_', trim($key)));
                if (!$this->isNoiseValue($val)) {
                    $normalizedRow[$normalizedKey] = $val;
                }
            }
            
            // Only add row if it has actual data
            $dataCount = count(array_filter($normalizedRow, fn($k, $v) => 
                !in_array(strtoupper($k), ['KEL_TARIF']) && $v !== null, ARRAY_FILTER_USE_BOTH));
            
            if ($dataCount > 0) {
                $grouped[$currentCategory][] = $normalizedRow;
            }
        }
        
        return $grouped;
    }

    /**
     * Detect noise rows: growth rates, all-placeholder values, empty
     */
    private function isNoiseRow(array $row): bool
    {
        $values = array_values(array_filter($row, fn($k, $v) => 
            !in_array(strtoupper($k), ['KEL. TARIF', 'KEL_TARIF', 'TARIF']), 
            ARRAY_FILTER_USE_BOTH));
        
        if (empty($values)) return true;
        
        // Check if all numeric values are placeholders (-1, -999) or small ratios
        $numericValues = array_filter($values, 'is_numeric');
        if (!empty($numericValues)) {
            $allPlaceholders = array_every($numericValues, fn($v) => in_array((float)$v, [-1, -999, -9999], true));
            $allSmallRatios = array_every($numericValues, fn($v) => abs($v) < 10 && $v != 0);
            $hasNegatives = count(array_filter($numericValues, fn($v) => $v < 0)) > 0;
            
            if ($allPlaceholders || ($allSmallRatios && $hasNegatives && count($numericValues) >= 3)) {
                return true;
            }
        }
        
        // Check for error strings
        foreach ($values as $v) {
            if (is_string($v) && in_array(strtoupper(trim($v)), ['#DIV/0!', '#N/A', '#REF!', ''])) {
                return true;
            }
        }
        
        return false;
    }

    private function containsIsoDate(array $row): bool
    {
        foreach ($row as $val) {
            if (is_string($val) && preg_match('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $val)) {
                return true;
            }
        }
        return false;
    }

    private function normalizeEra1997_2004(array $data): array
    {
        $normalized = [];
        $currentCat = 'UNTITLED';
        
        foreach ($data as $row) {
            if (!is_array($row)) continue;
            $tarif = $row['TARIF'] ?? $row['KEL_TARIF'] ?? '';
            
            if ($this->isEra1997CategoryTitle($tarif, $row)) {
                $currentCat = strtoupper(trim($tarif));
                continue;
            }
            if (strtolower(trim($tarif)) === 'tarif') continue;
            
            if (!isset($normalized[$currentCat])) {
                $normalized[$currentCat] = [];
            }
            $normalized[$currentCat][] = $row;
        }
        return $normalized;
    }

    private function isEra1997CategoryTitle(string $tarif, array $row): bool
    {
        $keywords = ['mwh jual', 'pelanggan', 'daya tersambung', 'penjualan'];
        $tarifLower = strtolower($tarif);
        foreach ($keywords as $kw) {
            if (str_contains($tarifLower, $kw)) {
                $others = array_filter($row, fn($k, $v) => 
                    !in_array(strtoupper($k), ['TARIF', 'KEL_TARIF']) && 
                    $v !== null && $v !== '' && $v !== 0, 
                    ARRAY_FILTER_USE_BOTH
                );
                return empty($others);
            }
        }
        return false;
    }
}

// Helper for PHP < 8.3
if (!function_exists('array_every')) {
    function array_every(array $array, callable $callback): bool {
        foreach ($array as $item) {
            if (!$callback($item)) return false;
        }
        return true;
    }
}