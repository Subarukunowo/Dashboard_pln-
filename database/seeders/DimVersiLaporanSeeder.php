<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DimVersiLaporanSeeder extends Seeder
{
    public function run()
    {
        $versi = [
            ['id_versi' => 1, 'nama_versi' => 'POJK-Non ISAK 112'],
            ['id_versi' => 2, 'nama_versi' => 'ISAK 112-PSAK 116'],
        ];

        foreach ($versi as $item) {
            DB::table('dim_versi_laporan')->updateOrInsert(
                ['id_versi' => $item['id_versi']], // Cek ID
                ['nama_versi' => $item['nama_versi']] // Update/Insert data
            );
        }
    }
}