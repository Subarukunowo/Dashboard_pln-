<?php

namespace App\Services\ETL;

use App\Models\FactHistoris;
use Illuminate\Support\Facades\Log;

class Loader
{
    /**
     * Ukuran batch untuk performa database yang optimal.
     */
    private int $batchSize = 500;

    /**
     * Memuat records ke dalam database menggunakan metode upsert.
     */
    public function load(array $records, int $tahun): array
    {
        $processed = 0;

        if (empty($records)) {
            return ['inserted' => 0, 'skipped' => 0];
        }

        try {
            // Memecah data menjadi potongan kecil agar tidak membebani memori/database
            foreach (array_chunk($records, $this->batchSize) as $batch) {
                $now = now();
                
                // Pastikan setiap row memiliki timestamp
                $rows = array_map(fn($r) => array_merge($r, [
                    'created_at' => $r['created_at'] ?? $now,
                    'updated_at' => $now,
                ]), $batch);

                /**
                 * upsert(values, uniqueBy, update)
                 * 1. values: Data yang akan dimasukkan.
                 * 2. uniqueBy: Kolom yang menentukan keunikan (sesuai constraint 'uq_fact' di DB kamu).
                 * 3. update: Kolom yang akan diperbarui jika data ditemukan.
                 */
                FactHistoris::upsert(
                    $rows,
                    ['tahun', 'bulan', 'is_triwulan', 'is_akumulasi', 'golongan_kode', 'metrik_kode'],
                    ['nilai', 'satuan', 'updated_at']
                );

                $processed += count($rows);
            }

            Log::info("ETL Loader: Berhasil memproses {$processed} records untuk tahun {$tahun} menggunakan upsert.");

        } catch (\Exception $e) {
            Log::error("ETL Loader error pada tahun {$tahun}: " . $e->getMessage());
            // Kita lempar kembali exception agar UI/Console tahu proses gagal
            throw $e; 
        }

        return ['inserted' => $processed, 'skipped' => 0];
    }
}