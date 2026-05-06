<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DimSubGolonganSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // Sosial (S)
            ['kode' => 'S-2/450VA', 'nama' => 'Sosial 450 VA', 'golongan_kode' => 'S', 'urutan' => 1],
            ['kode' => 'S-2/900VA', 'nama' => 'Sosial 900 VA', 'golongan_kode' => 'S', 'urutan' => 2],
            ['kode' => 'S-2/1300VA', 'nama' => 'Sosial 1300 VA', 'golongan_kode' => 'S', 'urutan' => 3],
            ['kode' => 'S-2/2200VA', 'nama' => 'Sosial 2200 VA', 'golongan_kode' => 'S', 'urutan' => 4],
            ['kode' => 'S-2/>2200-200kVA', 'nama' => 'Sosial >2200 VA s.d 200 kVA', 'golongan_kode' => 'S', 'urutan' => 5],
            ['kode' => 'S-3/>200kVA', 'nama' => 'Sosial >200 kVA', 'golongan_kode' => 'S', 'urutan' => 6],
            
            // Rumah Tangga (R)
            ['kode' => 'R-1/450VA', 'nama' => 'Rumah Tangga 450 VA', 'golongan_kode' => 'R', 'urutan' => 10],
            ['kode' => 'R-1/900VA', 'nama' => 'Rumah Tangga 900 VA', 'golongan_kode' => 'R', 'urutan' => 11],
            ['kode' => 'R-1/1300VA', 'nama' => 'Rumah Tangga 1300 VA', 'golongan_kode' => 'R', 'urutan' => 12],
            ['kode' => 'R-1/2200VA', 'nama' => 'Rumah Tangga 2200 VA', 'golongan_kode' => 'R', 'urutan' => 13],
            ['kode' => 'R-2/>2200-6600VA', 'nama' => 'Rumah Tangga >2200 s.d 6600 VA', 'golongan_kode' => 'R', 'urutan' => 14],
            ['kode' => 'R-3/>6600VA', 'nama' => 'Rumah Tangga >6600 VA', 'golongan_kode' => 'R', 'urutan' => 15],
            
            // Bisnis (B)
            ['kode' => 'B-1/450VA', 'nama' => 'Bisnis 450 VA', 'golongan_kode' => 'B', 'urutan' => 20],
            ['kode' => 'B-1/900VA', 'nama' => 'Bisnis 900 VA', 'golongan_kode' => 'B', 'urutan' => 21],
            ['kode' => 'B-1/1300VA', 'nama' => 'Bisnis 1300 VA', 'golongan_kode' => 'B', 'urutan' => 22],
            ['kode' => 'B-1/2200VA', 'nama' => 'Bisnis 2200 VA', 'golongan_kode' => 'B', 'urutan' => 23],
            ['kode' => 'B-2/>2200-200kVA', 'nama' => 'Bisnis >2200 VA s.d 200 kVA', 'golongan_kode' => 'B', 'urutan' => 24],
            ['kode' => 'B-3/>200kVA', 'nama' => 'Bisnis >200 kVA', 'golongan_kode' => 'B', 'urutan' => 25],
            
            // Industri (I)
            ['kode' => 'I-1/2200VA', 'nama' => 'Industri 2200 VA', 'golongan_kode' => 'I', 'urutan' => 30],
            ['kode' => 'I-1/2200-14000VA', 'nama' => 'Industri 2200 s.d 14000 VA', 'golongan_kode' => 'I', 'urutan' => 31],
            ['kode' => 'I-2/>14-200kVA', 'nama' => 'Industri >14 s.d 200 kVA', 'golongan_kode' => 'I', 'urutan' => 32],
            ['kode' => 'I-3/>200-5000kVA', 'nama' => 'Industri >200 kVA s.d 5.000 kVA', 'golongan_kode' => 'I', 'urutan' => 33],
            
            // Publik (P)
            ['kode' => 'P-1/450VA', 'nama' => 'Publik 450 VA', 'golongan_kode' => 'P', 'urutan' => 40],
            ['kode' => 'P-1/1300VA', 'nama' => 'Publik 1300 VA', 'golongan_kode' => 'P', 'urutan' => 41],
            ['kode' => 'P-1/2200VA', 'nama' => 'Publik 2200 VA', 'golongan_kode' => 'P', 'urutan' => 42],
            ['kode' => 'P-1/>2200-200kVA', 'nama' => 'Publik >2200 VA s.d 200 kVA', 'golongan_kode' => 'P', 'urutan' => 43],
            ['kode' => 'P-2/>200kVA', 'nama' => 'Publik >200 kVA', 'golongan_kode' => 'P', 'urutan' => 44],
            ['kode' => 'P3', 'nama' => 'Publik P3', 'golongan_kode' => 'P', 'urutan' => 45],
            
            // Multiguna (M)
            ['kode' => 'M/PS', 'nama' => 'Multiguna Penerangan Sementara', 'golongan_kode' => 'M', 'urutan' => 50],
            ['kode' => 'M/MB-Prabayar', 'nama' => 'Multiguna Prabayar', 'golongan_kode' => 'M', 'urutan' => 51],
        ];

        DB::table('rt_dim_sub_golongan')->upsert(
            $data,
            ['kode'],
            ['nama', 'golongan_kode', 'urutan', 'updated_at']
        );
    }
}