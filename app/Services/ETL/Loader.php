<?php

namespace App\Services\ETL;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Loader — Batch upsert ke tabel fact_historis
 *
 * Unique key untuk upsert:
 *   (tahun, bulan, is_triwulan, is_jumlah_tahunan, golongan_kode, metrik_kode)
 *
 * Update bila duplikat: nilai, satuan, sumber, updated_at
 */
class Loader
{
    private int $batchSize = 500;

    public function setChunkSize(int $size): self
    {
        $this->batchSize = max(100, min($size, 2000));
        return $this;
    }

    public function load(array $records, int $tahun): array
    {
        if (empty($records)) {
            return ['inserted' => 0, 'skipped' => 0];
        }

        $processed = 0;
        $now       = now();

        // Normalisasi record: pastikan semua field yang dibutuhkan ada
        $normalized = array_map(function (array $r) use ($now): array {
            return [
                'tahun'             => (int)$r['tahun'],
                'bulan'             => (int)$r['bulan'],
                'is_triwulan'       => (bool)($r['is_triwulan'] ?? false),
                'is_akumulasi'      => (bool)($r['is_akumulasi'] ?? false),
                'is_jumlah_tahunan' => (bool)($r['is_jumlah_tahunan'] ?? false),
                'golongan_kode'     => $r['golongan_kode'],
                'metrik_kode'       => $r['metrik_kode'],
                'nilai'             => (float)$r['nilai'],
                'satuan'            => $r['satuan'] ?? '',
                'sumber'            => $r['sumber'] ?? 'import',
                'created_at'        => $r['created_at'] ?? $now,
                'updated_at'        => $now,
            ];
        }, $records);

        try {
            foreach (array_chunk($normalized, $this->batchSize) as $batch) {
                \App\Models\FactHistoris::upsert(
                    $batch,
                    // Unique key — harus matching indeks unik di tabel
                    ['tahun', 'bulan', 'is_triwulan', 'is_jumlah_tahunan', 'golongan_kode', 'metrik_kode'],
                    // Kolom yang di-update bila duplicate
                    ['nilai', 'satuan', 'sumber', 'updated_at']
                );
                $processed += count($batch);
            }

            return ['inserted' => $processed, 'skipped' => 0];

        } catch (\Throwable $e) {
            Log::error("Loader error tahun {$tahun}: " . $e->getMessage(), [
                'sample_record' => $normalized[0] ?? null,
            ]);
            throw $e;
        }
    }
}