<?php

// database/seeders/PlnBatamDimensionSeeder.php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Dimension\{DimTahun, DimPeriode, DimGolonganTarif, DimMetrik};

class PlnBatamDimensionSeeder extends Seeder {
    public function run(): void {
        foreach (range(1997, 2025) as $t) DimTahun::firstOrCreate(['tahun' => $t]);

        $periods = [
            ['JAN','Januari',1,'bulanan'],['FEB','Februari',2,'bulanan'],['MAR','Maret',3,'bulanan'],
            ['APR','April',4,'bulanan'],['MAY','Mei',5,'bulanan'],['JUN','Juni',6,'bulanan'],
            ['JUL','Juli',7,'bulanan'],['AUG','Agustus',8,'bulanan'],['SEP','September',9,'bulanan'],
            ['OCT','Oktober',10,'bulanan'],['NOV','November',11,'bulanan'],['DEC','Desember',12,'bulanan'],
            ['TRW1','Triwulan 1',1,'triwulan'],['TRW2','Triwulan 2',2,'triwulan'],
            ['TRW3','Triwulan 3',3,'triwulan'],['TRW4','Triwulan 4',4,'triwulan'],
            ['TOTAL','Total Tahunan',null,'total'],['RKAP','Target RKAP',null,'rkap']
        ];
        foreach ($periods as $p) DimPeriode::firstOrCreate(['kode'=>$p[0]], ['nama'=>$p[1],'urutan'=>$p[2],'tipe'=>$p[3]]);

        $gol = [
            ['S','SOSIAL','#ff9800'],['R','RUMAHTANGGA','#00ff88'],['B','BISNIS','#2196f3'],
            ['I','INDUSTRI','#e91e63'],['P','PEMERINTAH','#9c27b0'],['M','MULTIGUNA/PRABAYAR','#00bcd4']
        ];
        foreach ($gol as $g) DimGolonganTarif::firstOrCreate(['kode'=>$g[0]], ['nama'=>$g[1],'warna'=>$g[2]]);

        $metriks = [
            ['KWH_JUAL','KWH/MWH Jual','MWh'],['RUPIAH_PENJUALAN','Penjualan (Ribu/IDR)','IDR'],
            ['PELANGGAN','Jumlah Pelanggan','Unit'],['DAYA_TERSAMBUNG','Daya Tersambung (VA/kVA)','VA'],
            ['RP_PER_KWH','Tarif Rata-rata','Rp/kWh']
        ];
        foreach ($metriks as $m) DimMetrik::firstOrCreate(['kode'=>$m[0]], ['nama'=>$m[1],'satuan'=>$m[2]]);
    }
}