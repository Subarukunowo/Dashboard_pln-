<?php

namespace App\Services\ETL;

use Illuminate\Support\Facades\Log;

class Transformer
{
    private const JENIS_MAP = [
        'mwh jual' => 'mwh_jual', 'kwh jual' => 'kwh_jual',
        'rupiah penjualan' => 'rupiah', 'penjualan (ribuan' => 'rupiah',
        'pelanggan' => 'pelanggan', 'pelanggan akumulasi' => 'pelanggan',
        'daya tersambung' => 'daya_va', 'rp/kwh jual' => 'rpkwh',
        '% pertumbuhan' => 'pertumbuhan_yoy', 'growth' => 'pertumbuhan_yoy',
    ];

    private const GOLONGAN_MAP = [
        'sosial' => 'S', 's (sosial)' => 'S',
        'residensial' => 'R', 'rumahtangga' => 'R', 'rumah tangga' => 'R', 'r (residensial)' => 'R',
        'bisnis' => 'B', 'b (bisnis)' => 'B',
        'industri' => 'I', 'i (industri)' => 'I',
        'publik' => 'P', 'pemerintah' => 'P', 'publik/ pemerintah' => 'P', 'p (publik/ pemerintah)' => 'P',
        'multiguna' => 'M', 'multiguna / prabayar' => 'M', 'multiguna /prabayar' => 'M', 'm (multiguna)' => 'M',
        'jumlah' => 'TOTAL', 'total' => 'TOTAL',
    ];

    private const SATUAN_MAP = [
        'kwh_jual' => 'KWH', 'mwh_jual' => 'MWH', 'rupiah' => 'Rp',
        'pelanggan' => 'pelanggan', 'daya_va' => 'VA',
        'rpkwh' => 'Rp/kWh', 'pertumbuhan_yoy' => '%',
    ];

    /**
     * ✅ Dynamic key validation - NO MORE hardcoded array
     */
    private function isValidDataKey(string $key, int $tahun): bool
    {
        $keyUpper = strtoupper(trim($key));
        
        // Plain month names (for 1997-2004, 2025+ eras)
        if (preg_match('/^(JAN|FEB|MAR|APR|MEI|JUN|JUL|AGU|SEP|OKT|NOV|DES)$/i', $keyUpper)) {
            return true;
        }
        
        // Month with hyphen + 2-digit year: JAN-05 to DES-99
        if (preg_match('/^(JAN|FEB|MAR|APR|MEI|JUN|JUL|AGU|SEP|OKT|NOV|DES)-\d{2}$/i', $keyUpper)) {
            return true;
        }
        
        // Month with underscore + 4-digit year (fallback)
        if (preg_match('/^(JAN|FEB|MAR|APR|MEI|JUN|JUL|AGU|SEP|OKT|NOV|DES)_\d{4}$/i', $keyUpper)) {
            return true;
        }
        
        // Special columns with optional year suffix
        if (preg_match('/^(JUMLAH|TOTAL|MUTASI|RKAP|PENCAPAIAN|RATA-RATA)(_|\s+)?\d{4}?$/i', $keyUpper)) {
            return true;
        }
        
        // Triwulan keys
        if (preg_match('/^TRW[_\s\-]?[IVX]+$/i', $keyUpper)) {
            return true;
        }
        
        return false;
    }

    /**
     * Filter noise values (shared with Extractor logic)
     */
    private function isNoiseValue($val): bool
    {
        if ($val === null || $val === '' || $val === '-' || $val === 'NULL') return true;
        if (is_string($val)) {
            $trimmed = strtoupper(trim($val));
            if (in_array($trimmed, ['#DIV/0!', '#N/A', '#REF!', '#VALUE!', ''])) return true;
        }
        if (is_numeric($val) && in_array((float) $val, [-1, -999, -9999], true)) return true;
        return false;
    }

    /**
     * Detect growth rate rows (not actual data)
     */
    private function isGrowthRateRow(array $row, string $jenis): bool
    {
        if ($jenis === 'pertumbuhan_yoy') return false; // Don't filter actual growth data
        
        $values = array_filter($row, fn($k, $v) => 
            !in_array(strtoupper($k), ['KEL_TARIF', 'KEL. TARIF', 'TARIF']) && is_numeric($v),
            ARRAY_FILTER_USE_BOTH);
        
        if (empty($values)) return false;
        
        // Growth rates: small values (-10 < x < 10) with negatives present
        $allSmall = array_every($values, fn($v) => abs($v) < 10 && $v != 0);
        $hasNegative = count(array_filter($values, fn($v) => $v < 0)) > 0;
        
        return $allSmall && $hasNegative && count($values) >= 3;
    }

    public function transform(array $rawData, int $tahun): array
    {
        if (empty($rawData)) return [];

        $records = [];
        $isGrouped = !array_is_list($rawData);

        if ($isGrouped) {
            foreach ($rawData as $kategori => $rows) {
                if (!is_array($rows)) continue;
                $jenis = $this->detectJenis($kategori);
                if (!$jenis) continue;

                foreach ($rows as $row) {
                    if (!is_array($row)) continue;
                    $parsed = $this->processRow($row, $tahun, $jenis);
                    if (!empty($parsed)) {
                        $records = array_merge($records, $parsed);
                    }
                }
            }
        } else {
            $currentJenis = null;
            foreach ($rawData as $row) {
                if (!is_array($row)) continue;
                
                $label = $row['TARIF'] ?? $row['KEL_TARIF'] ?? $row['KEL. TARIF'] ?? '';
                $labelLower = strtolower(trim((string) $label));

                if ($this->isCategoryTitle($labelLower, $row)) {
                    $currentJenis = $this->detectJenis($label);
                    continue;
                }
                if (in_array($labelLower, ['tarif', 'kel. tarif'])) continue;
                if (!$currentJenis) continue;

                $parsed = $this->processRow($row, $tahun, $currentJenis);
                if (!empty($parsed)) {
                    $records = array_merge($records, $parsed);
                }
            }
        }

        return $records;
    }

    private function processRow(array $row, int $tahun, string $jenis): array
    {
        $golRaw = $row['KEL_TARIF'] ?? $row['KEL. TARIF'] ?? $row['TARIF'] ?? '';
        $golongan = $this->normalizeGolongan($golRaw);
        
        if (!$golongan || $golongan === 'TOTAL') return [];
        
        // ✅ Skip growth rate rows (noise)
        if ($this->isGrowthRateRow($row, $jenis)) return [];
        
        $isTriwulan = $this->isTriwulanFormat($row);
        return $isTriwulan 
            ? $this->parseTriwulanRow($row, $tahun, $golongan, $jenis)
            : $this->parseBulananRow($row, $tahun, $golongan, $jenis);
    }

    private function parseTriwulanRow(array $row, int $tahun, string $golongan, string $jenis): array
    {
        $records = [];
        $mapping = [
            'TRW_I' => 3, 'TRW II' => 3, 'TRW_II' => 6, 
            'TRW_III' => 9, 'TRW III' => 9, 'TRW_IV' => 12, 'TRW IV' => 12,
        ];

        $processed = [];
        foreach ($mapping as $key => $bulan) {
            if (isset($row[$key]) && !isset($processed[$bulan])) {
                $angka = $this->parseAngka($row[$key]);
                if ($angka !== null && !$this->isNoiseValue($angka)) {
                    if ($jenis === 'rupiah') $angka *= 1000;
                    $records[] = $this->makeRecord($tahun, $bulan, true, false, false, $golongan, $jenis, $angka);
                    $processed[$bulan] = true;
                }
            }
        }

        if (isset($row['JUMLAH']) || isset($row['TOTAL'])) {
            $val = $row['JUMLAH'] ?? $row['TOTAL'] ?? null;
            $angka = $this->parseAngka($val);
            if ($angka !== null && !$this->isNoiseValue($angka)) {
                if ($jenis === 'rupiah') $angka *= 1000;
                $records[] = $this->makeRecord($tahun, 12, false, false, true, $golongan, $jenis, $angka);
            }
        }
        return $records;
    }

    private function parseBulananRow(array $row, int $tahun, string $golongan, string $jenis): array
    {
        $records = [];
        
        // Month → number mapping (base)
        $bulanMap = [
            'JAN'=>1, 'FEB'=>2, 'MAR'=>3, 'APR'=>4, 'MAY'=>5, 'JUN'=>6,
            'JUL'=>7, 'AUG'=>8, 'SEP'=>9, 'OCT'=>10, 'NOV'=>11, 'DEC'=>12,
            'MEI'=>5, 'AGU'=>8, 'OKT'=>10, 'DES'=>12,
        ];
        
        // ✅ Generate dynamic mapping for ALL suffixes 00-99 (covers 1900-2099)
        foreach (range(0, 99) as $y) {
            $suffix = str_pad($y, 2, '0', STR_PAD_LEFT);
            foreach (array_keys($bulanMap) as $m) {
                $bulanMap["{$m}-{$suffix}"] = $bulanMap[$m];
            }
        }

        $processed = [];
        foreach ($row as $key => $val) {
            $keyUpper = strtoupper(trim((string) $key));
            
            // ✅ Use dynamic validation instead of hardcoded array
            if (!$this->isValidDataKey($keyUpper, $tahun)) continue;
            if (isset($processed[$keyUpper])) continue;

            // Lookup bulan (handle plain + suffixed keys)
            $bulan = $bulanMap[$keyUpper] ?? null;
            
            // Handle special columns
            if (preg_match('/^(JUMLAH|TOTAL|MUTASI|RKAP|PENCAPAIAN)/i', $keyUpper)) {
                $bulan = 0;
            }
            
            if ($bulan === null) continue;
            if ($this->isNoiseValue($val)) continue;

            $angka = $this->parseAngka($val);
            if ($angka === null) continue;
            
            // ✅ Filter suspicious values per metric type
            if ($this->isValueSuspicious($angka, $jenis)) continue;

            if ($jenis === 'rupiah') $angka *= 1000;
            $bulanFinal = ($bulan === 0) ? 12 : $bulan;

            $records[] = $this->makeRecord(
                $tahun, $bulanFinal, false, false, $bulan === 0,
                $golongan, $jenis, $angka
            );
            $processed[$keyUpper] = true;
        }

        return $records;
    }

    /**
     * Filter values that don't make sense for the metric type
     */
    private function isValueSuspicious(float $value, string $jenis): bool
    {
        if ($jenis === 'kwh_jual' || $jenis === 'mwh_jual') {
            return $value > 0 && $value < 100; // KWH shouldn't be tiny
        }
        if ($jenis === 'rupiah') {
            return $value > 0 && $value < 1000; // Rupiah (in thousands) shouldn't be tiny
        }
        if ($jenis === 'rpkwh') {
            return $value < 100 || $value > 50000; // Rp/kWh typical range
        }
        if ($jenis === 'pelanggan' || $jenis === 'daya_va') {
            return $value < 0 || ($value > 0 && $value < 1 && floor($value) != $value);
        }
        if ($jenis === 'pertumbuhan_yoy') {
            return $value < -100 || $value > 1000; // Growth % typical range
        }
        return false;
    }

    private function isCategoryTitle(string $label, array $row): bool
    {
        $keywords = array_keys(self::JENIS_MAP);
        foreach ($keywords as $kw) {
            if (str_contains($label, $kw)) {
                $hasData = false;
                foreach ($row as $k => $v) {
                    if (!in_array(strtoupper($k), ['TARIF', 'KEL_TARIF', 'KEL. TARIF']) && 
                        $v !== null && $v !== '' && $v !== 0) {
                        $hasData = true; break;
                    }
                }
                return !$hasData;
            }
        }
        return false;
    }

    private function detectJenis($label): ?string
    {
        if (!$label) return null;
        $lower = strtolower(trim((string) $label));
        foreach (self::JENIS_MAP as $kw => $kode) {
            if (str_contains($lower, $kw)) return $kode;
        }
        return null;
    }

    private function normalizeGolongan($val): ?string
    {
        if (!$val) return null;
        $lower = strtolower(trim((string) $val));
        if (isset(self::GOLONGAN_MAP[$lower])) return self::GOLONGAN_MAP[$lower];
        foreach (self::GOLONGAN_MAP as $kw => $kode) {
            if (str_starts_with($lower, $kw)) return $kode;
        }
        return null;
    }

    private function isTriwulanFormat(array $row): bool
    {
        $keys = implode('|', array_keys($row));
        return (bool) preg_match('/TRW[_\s\-]?[IVX]+/i', $keys);
    }

    public function parseAngka($val): ?float
    {
        if ($val === null || $val === '' || $val === '-' || $val === '#REF!' || $val === '#N/A' || $val === '#DIV/0!') return null;
        if (is_numeric($val)) return (float) $val;
        
        $str = (string) $val;
        
        // European format: 1.234,56
        if (preg_match('/^\d{1,3}(?:\.\d{3})*(?:,\d+)?$/', $str)) {
            $clean = str_replace('.', '', $str);
            $clean = str_replace(',', '.', $clean);
            return is_numeric($clean) ? (float) $clean : null;
        }
        // US format: 1,234.56
        if (preg_match('/^\d{1,3}(?:,\d{3})*(?:\.\d+)?$/', $str)) {
            $clean = str_replace(',', '', $str);
            return is_numeric($clean) ? (float) $clean : null;
        }
        // Fallback
        $clean = preg_replace('/[^0-9.\-]/', '', $str);
        return is_numeric($clean) && $clean !== '' ? (float) $clean : null;
    }

    private function makeRecord($tahun, $bulan, $isTw, $isAkum, $isJumlah, $gol, $metrik, $nilai)
    {
        return [
            'tahun' => $tahun, 'bulan' => $bulan,
            'is_triwulan' => $isTw, 'is_akumulasi' => $isAkum, 'is_jumlah_tahunan' => $isJumlah,
            'golongan_kode' => $gol, 'metrik_kode' => $metrik,
            'nilai' => $nilai, 'satuan' => self::SATUAN_MAP[$metrik] ?? '',
            'sumber' => 'import', 'created_at' => now(), 'updated_at' => now(),
        ];
    }
}

if (!function_exists('array_is_list')) {
    function array_is_list(array $array): bool {
        if ($array === []) return true;
        return array_keys($array) === range(0, count($array) - 1);
    }
}
if (!function_exists('array_every')) {
    function array_every(array $array, callable $callback): bool {
        foreach ($array as $item) {
            if (!$callback($item)) return false;
        }
        return true;
    }
}