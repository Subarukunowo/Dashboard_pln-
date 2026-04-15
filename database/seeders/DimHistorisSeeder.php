<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DimHistorisSeeder extends Seeder
{
    public function run(): void
    {
        // Golongan
        DB::table('dim_golongan')->upsert([
            ['kode'=>'S', 'nama'=>'Sosial',        'alias'=>json_encode(['sosial','s (sosial)','s']),                     'urutan'=>1],
            ['kode'=>'R', 'nama'=>'Rumah Tangga',  'alias'=>json_encode(['rumahtangga','rumah tangga','residensial','r']),'urutan'=>2],
            ['kode'=>'B', 'nama'=>'Bisnis',        'alias'=>json_encode(['bisnis','b','b (bisnis)']),                     'urutan'=>3],
            ['kode'=>'I', 'nama'=>'Industri',      'alias'=>json_encode(['industri','i','i (industri)']),                 'urutan'=>4],
            ['kode'=>'P', 'nama'=>'Publik',        'alias'=>json_encode(['publik','pemerintah','publik/ pemerintah','p']),'urutan'=>5],
            ['kode'=>'M', 'nama'=>'Multiguna',     'alias'=>json_encode(['multiguna','multiguna / prabayar','m']),        'urutan'=>6],
            ['kode'=>'TOTAL','nama'=>'Total',      'alias'=>json_encode(['jumlah','total']),                              'urutan'=>7],
        ], ['kode'], ['nama','alias','urutan']);

        // Metrik
        DB::table('dim_metrik')->upsert([
            ['kode'=>'kwh_jual',        'nama'=>'KWH Jual',                    'satuan'=>'KWH',      'kategori'=>'penjualan'],
            ['kode'=>'mwh_jual',        'nama'=>'MWH Jual',                    'satuan'=>'MWH',      'kategori'=>'penjualan'],
            ['kode'=>'rupiah',          'nama'=>'Rupiah Penjualan',            'satuan'=>'Rp',       'kategori'=>'pendapatan'],
            ['kode'=>'pelanggan',       'nama'=>'Pelanggan',                   'satuan'=>'pelanggan','kategori'=>'pelanggan'],
            ['kode'=>'daya_va',         'nama'=>'Daya Tersambung',             'satuan'=>'VA',       'kategori'=>'pelanggan'],
            ['kode'=>'rpkwh',           'nama'=>'RP/KWH Jual',                 'satuan'=>'Rp/kWh',   'kategori'=>'tarif'],
            ['kode'=>'pertumbuhan_yoy', 'nama'=>'Pertumbuhan YoY Kumulatif',   'satuan'=>'%',        'kategori'=>'pertumbuhan'],
            ['kode'=>'delta_gwh',       'nama'=>'Delta GWh',                   'satuan'=>'GWh',      'kategori'=>'penjualan'],
            ['kode'=>'growth',          'nama'=>'Growth',                      'satuan'=>'%',        'kategori'=>'pertumbuhan'],
            ['kode'=>'komposisi',       'nama'=>'Komposisi',                   'satuan'=>'%',        'kategori'=>'pertumbuhan'],
        ], ['kode'], ['nama','satuan','kategori']);

        // Periode
        $periodes = [
            [1997,'triwulan',['mwh_jual','rupiah','pelanggan','daya_va']],
            [1998,'triwulan',['mwh_jual','rupiah','pelanggan','daya_va']],
            [1999,'triwulan',['mwh_jual','rupiah','pelanggan','daya_va']],
            [2000,'triwulan',['mwh_jual','rupiah','pelanggan','daya_va']],
            [2001,'triwulan',['mwh_jual','rupiah','pelanggan','daya_va']],
            [2002,'triwulan',['mwh_jual','rupiah','pelanggan','daya_va']],
            [2003,'triwulan',['mwh_jual','rupiah','pelanggan','daya_va']],
            [2004,'triwulan',['mwh_jual','rupiah','pelanggan','daya_va']],
            [2005,'bulanan', ['kwh_jual','rupiah','pelanggan','daya_va']],
            [2006,'bulanan', ['kwh_jual','rupiah','pelanggan','daya_va']],
            [2007,'bulanan', ['kwh_jual','rupiah','pelanggan','daya_va']],
            [2008,'bulanan', ['kwh_jual','rupiah','pelanggan','daya_va']],
            [2009,'bulanan', ['kwh_jual','rupiah','pelanggan','daya_va']],
            [2010,'bulanan', ['kwh_jual','rupiah','pelanggan','daya_va','rpkwh']],
            [2011,'bulanan', ['kwh_jual','rupiah','pelanggan','daya_va','rpkwh']],
            [2012,'bulanan', ['kwh_jual','rupiah','pelanggan','daya_va','rpkwh']],
            [2013,'bulanan', ['kwh_jual','rupiah','pelanggan','daya_va','rpkwh']],
            [2014,'bulanan', ['kwh_jual','rupiah','pelanggan','daya_va','rpkwh']],
            [2015,'bulanan', ['kwh_jual','rupiah','pelanggan','daya_va','rpkwh']],
            [2016,'bulanan', ['kwh_jual','rupiah','pelanggan','daya_va','rpkwh']],
            [2017,'bulanan', ['kwh_jual','rupiah','pelanggan','daya_va','rpkwh','pertumbuhan_yoy']],
            [2018,'bulanan', ['kwh_jual','rupiah','pelanggan','daya_va','rpkwh','pertumbuhan_yoy']],
            [2019,'bulanan', ['kwh_jual','rupiah','pelanggan','daya_va','rpkwh','pertumbuhan_yoy']],
            [2020,'bulanan', ['kwh_jual','rupiah','pelanggan','daya_va','rpkwh']],
            [2021,'bulanan', ['kwh_jual','rupiah','pelanggan','daya_va','rpkwh']],
            [2022,'bulanan', ['kwh_jual','rupiah','pelanggan','daya_va','rpkwh']],
            [2023,'bulanan', ['kwh_jual','rupiah','pelanggan','daya_va','rpkwh']],
            [2024,'bulanan', ['kwh_jual','rupiah','pelanggan','daya_va','rpkwh','delta_gwh','growth','komposisi']],
            [2025,'bulanan', ['kwh_jual','rupiah','pelanggan','daya_va','rpkwh','pertumbuhan_yoy']],
        ];

        foreach ($periodes as [$tahun, $gran, $metrik]) {
            DB::table('dim_periode')->upsert([
                ['tahun'=>$tahun,'granularitas'=>$gran,'metrik_tersedia'=>json_encode($metrik),'catatan'=>null]
            ], ['tahun'], ['granularitas','metrik_tersedia']);
        }
    }
}