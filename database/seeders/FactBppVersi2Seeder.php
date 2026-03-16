<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FactBppVersi2Seeder extends Seeder
{
    public function run()
    {
        // Mendapatkan ID Versi 2 secara dinamis
        $idVersi = DB::table('dim_versi_laporan')->where('nama_versi', 'ISAK 112-PSAK 116')->value('id_versi');

        // Data khusus versi 2
        // Format: [nama_bpp, [data_jan, feb, mar, ...]]
        $dataset = [
            ['BPP PEMBANGKIT', [null, 1.468, 1.470, 1.471, 1.469, 1.484, 1.487, 1.481, 1.483, 1.492, 1.482, 1.514]],
            ['BPP S.D TRANSMISI', [null, 1.505, 1.505, 1.506, 1.505, 1.521, 1.522, 1.516, 1.527, 1.528, 1.520, 1.558]],
            ['BPP S.D DIDTRIBUSI TM', [null, 1.543, 1.535, 1.536, 1.538, 1.556, 1.560, 1.552, 1.563, 1.566, 1.560, 1.597]],
            ['BPP S.D DIDTRIBUSI TR', [null, 1.616, 1.589, 1.591, 1.600, 1.623, 1.632, 1.619, 1.633, 1.639, 1.637, 1.673]],
        ];

        foreach ($dataset as $row) {
            $idJenis = DB::table('dim_jenis_bpp')->where('nama_bpp', $row[0])->value('id_jenis_bpp');
            
            foreach ($row[1] as $index => $nilai) {
                if ($nilai !== null) { // Skip jika kosong/null
                    DB::table('fact_bpp')->updateOrInsert(
                        [
                            'id_waktu'     => $index + 1,
                            'id_jenis_bpp' => $idJenis,
                            'id_versi'     => $idVersi
                        ],
                        ['nilai_bpp' => $nilai]
                    );
                }
            }
        }
    }
}