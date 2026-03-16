<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FactBppSeeder extends Seeder
{
    public function run()
    {
        // 1. Data per baris (Jenis BPP)
        $dataBpp = [
            'BPP PEMBANGKIT' => [1.511, 1.477, 1.477, 1.478, 1.521, 1.464, 1.477, 1.469, 1.472, 1.485, 1.471, 1.501],
            'BPP S.D TRANSMISI' => [1.546, 1.517, 1.516, 1.516, 1.561, 1.505, 1.516, 1.508, 1.519, 1.524, 1.513, 1.548],
            'BPP S.D DIDTRIBUSI TM' => [1.579, 1.557, 1.548, 1.548, 1.596, 1.544, 1.556, 1.546, 1.558, 1.564, 1.555, 1.589],
            'BPP S.D DIDTRIBUSI TR' => [1.640, 1.633, 1.605, 1.607, 1.661, 1.618, 1.633, 1.619, 1.633, 1.640, 1.637, 1.669],
        ];

        // 2. Loop untuk memproses data
        foreach ($dataBpp as $namaBpp => $nilaiBulanan) {
            $idJenis = DB::table('dim_jenis_bpp')->where('nama_bpp', $namaBpp)->value('id_jenis_bpp');
            
            foreach ($nilaiBulanan as $index => $nilai) {
                DB::table('fact_bpp')->updateOrInsert(
                    [
                        'id_waktu'     => $index + 1, // ID bulan 1-12
                        'id_jenis_bpp' => $idJenis,
                        'id_versi'     => 1 // Asumsi ID 1 untuk POJK-Non ISAK
                    ],
                    ['nilai_bpp' => $nilai]
                );
            }
        }
    }
}