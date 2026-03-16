<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DimJenisBppSeeder extends Seeder
{
    public function run()
    {
        $jenisBpp = [
            ['id_jenis_bpp' => 1, 'nama_bpp' => 'BPP PEMBANGKIT'],
            ['id_jenis_bpp' => 2, 'nama_bpp' => 'BPP S.D TRANSMISI'],
            ['id_jenis_bpp' => 3, 'nama_bpp' => 'BPP S.D DIDTRIBUSI TM'],
            ['id_jenis_bpp' => 4, 'nama_bpp' => 'BPP S.D DIDTRIBUSI TR'],
        ];

        foreach ($jenisBpp as $item) {
            DB::table('dim_jenis_bpp')->updateOrInsert(
                ['id_jenis_bpp' => $item['id_jenis_bpp']],
                ['nama_bpp' => $item['nama_bpp']]
            );
        }
    }
}