<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DimWaktuSeeder extends Seeder
{
    public function run()
    {
        $bulan = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        foreach ($bulan as $index => $nama) {
            DB::table('dim_waktu')->insert([
                // Kita gunakan $index + 1 agar ID mulai dari 1 sampai 12
                'id_waktu'    => $index + 1, 
                'tahun'       => 2025,
                'bulan_angka' => $index + 1,
                'nama_bulan'  => $nama,
            ]);
        }
    }
}