<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rt_fact_kva', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun');
            $table->unsignedTinyInteger('bulan');
            $table->boolean('is_triwulan')->default(0);
            $table->boolean('is_akumulasi')->default(0);
            $table->enum('tipe', ['target', 'realisasi']);
            $table->string('sub_golongan_kode', 30);
            $table->string('golongan_kode', 10)->nullable();
            $table->decimal('nilai_kva', 15, 2)->default(0);
            $table->string('sumber', 20)->default('manual');
            $table->timestamps();

            $table->unique(
                ['tahun', 'bulan', 'is_triwulan', 'is_akumulasi', 'tipe', 'sub_golongan_kode'],
                'rt_kva_unique'
            );
            $table->index(['tahun', 'bulan', 'golongan_kode'], 'rt_kva_tahun_bulan_golongan_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rt_fact_kva');
    }
};