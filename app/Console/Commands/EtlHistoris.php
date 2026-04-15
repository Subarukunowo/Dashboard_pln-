<?php

namespace App\Console\Commands;

use App\Services\ETL\Extractor;
use App\Services\ETL\Transformer;
use App\Services\ETL\Loader;
use App\Models\DimTahunPeriode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class EtlHistoris extends Command
{
    /**
     * Signature ditambahkan --all untuk memproses semua file sekaligus
     */
    protected $signature = 'etl:historis {--tahun=} {--file=} {--dry-run} {--all}';
    protected $description = 'Proses ETL data historis penjualan PLN Batam (1997-2025)';

    public function __construct(
        private Extractor   $extractor,
        private Transformer $transformer,
        private Loader      $loader
    ) { 
        parent::__construct(); 
    }

    public function handle(): int
    {
        // Gunakan Storage path agar lebih aman di Windows/XAMPP
        $importDir = storage_path('app/imports');
        
        if (!is_dir($importDir)) {
            $this->error("Direktori tidak ditemukan: {$importDir}");
            $this->info("Silakan buat folder 'imports' di dalam storage/app/");
            return self::FAILURE;
        }

        $tahunOpt = $this->option('tahun');
        $fileOpt  = $this->option('file');
        $dryRun   = $this->option('dry-run');

        // Logika pemilihan file
        $files = $this->getFiles($importDir, $fileOpt);

        if (empty($files)) {
            $this->error("Tidak ada file Excel/CSV yang ditemukan di {$importDir}");
            return self::FAILURE;
        }

        $this->info("Ditemukan " . count($files) . " file untuk diproses.");

        foreach ($files as $filePath) {
            $fileName = basename($filePath);
            
            // Prioritas tahun: 1. Option --tahun, 2. Regex dari nama file
            $tahun = $tahunOpt ? (int)$tahunOpt : $this->extractTahun($fileName);

            if (!$tahun) {
                $this->warn("⚠️ Skip {$fileName}: Tahun tidak terdeteksi (Gunakan --tahun=XXXX)");
                continue;
            }

            $this->newLine();
            $this->info("🚀 Memulai proses: {$fileName} | Tahun: {$tahun}");

            try {
                // 1. EXTRACT
                $sheets = $this->extractor->fromExcel($filePath);
                $this->line("   ✅ Extract: " . count($sheets) . " sheet(s)");

                // 2. TRANSFORM
                $records = $this->matchTransformer($sheets, $tahun);
                $this->line("   ✅ Transform: " . count($records) . " records didapatkan");

                // 3. DRY RUN (SIMULASI)
                if ($dryRun) {
                    $this->showDryRunTable($records);
                    continue;
                }

                // 4. LOAD (DATABASE)
                if (empty($records)) {
                    $this->warn("   ⚠️ Tidak ada data valid untuk disimpan.");
                    continue;
                }

                $result = $this->loader->load($records, $tahun);
                $this->info("   ✅ Success: {$result['inserted']} records masuk ke database.");

                // Update Metadata Periode
                $this->updateMetadata($tahun, $records);

            } catch (\Exception $e) {
                $this->error("   ❌ Error pada {$fileName}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("✨ Selesai!");
        return self::SUCCESS;
    }

    /**
     * Mencocokkan fungsi transformer berdasarkan rentang tahun
     */
    private function matchTransformer(array $sheets, int $tahun): array
    {
        return match(true) {
            $tahun <= 2004 => $this->transformer->transformTriwulan($sheets, $tahun),
            $tahun === 2024 => $this->transformer->transform2024($sheets, $tahun),
            default         => $this->transformer->transformBulanan($sheets, $tahun),
        };
    }

    /**
     * Filter file berdasarkan input user
     */
    private function getFiles(string $dir, ?string $fileOpt): array
    {
        if ($fileOpt) {
            $path = "{$dir}/" . str_replace('imports/', '', $fileOpt);
            return file_exists($path) ? [$path] : [];
        }

        return glob("{$dir}/*.{xlsx,xls,csv}", GLOB_BRACE);
    }

    private function extractTahun(string $filename): ?int
    {
        preg_match('/(\d{4})/', $filename, $m);
        $t = isset($m[1]) ? (int)$m[1] : null;
        return ($t && $t >= 1997 && $t <= 2030) ? $t : null;
    }

    private function showDryRunTable(array $records): void
    {
        $this->warn("   [DRY RUN] Menampilkan 10 data pertama:");
        
        $sample = array_slice($records, 0, 10);
        $tableData = array_map(fn($r) => [
            $r['tahun'], 
            $r['bulan'] ?? 'N/A', 
            $r['golongan_kode'], 
            $r['metrik_kode'] ?? $r['jenis_data'], 
            number_format($r['nilai'], 2)
        ], $sample);

        $this->table(['Tahun', 'Bulan', 'Gol', 'Metrik', 'Nilai'], $tableData);
    }

private function updateMetadata(int $tahun, array $records): void
{
    // Gunakan array_values agar index JSON-nya rapi
    $metrik = array_values(array_unique(array_column($records, 'metrik_kode')));

    \App\Models\DimPeriode::updateOrCreate(
        ['tahun' => $tahun],
        [
            'granularitas'   => ($tahun <= 2004) ? 'triwulan' : 'bulanan',
            'metrik_tersedia' => json_encode($metrik),
            'updated_at'      => now(),
        ]
    );
}

    private function detectFormat(int $tahun): string
    {
        return match(true) {
            $tahun <= 2004  => 'triwulan_1997',
            $tahun <= 2023  => 'bulanan_standar',
            $tahun === 2024 => 'bulanan_2024',
            default         => 'bulanan_future',
        };
    }
}