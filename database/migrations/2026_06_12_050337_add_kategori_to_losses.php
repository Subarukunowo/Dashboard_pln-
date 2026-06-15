<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rt_fact_losses', function (Blueprint $table) {
            $table->enum('kategori', ['transmisi', 'distribusi', 'jaringan'])
                ->default('transmisi')
                ->after('tipe_losses');
        });

        Schema::table('rt_fact_losses', function (Blueprint $table) {
            $table->dropUnique('rt_losses_unique');
            $table->unique(
                ['tahun', 'bulan', 'is_triwulan', 'is_akumulasi', 'tipe_losses', 'kategori', 'tipe'],
                'rt_losses_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('rt_fact_losses', function (Blueprint $table) {
            $table->dropUnique('rt_losses_unique');
            $table->dropColumn('kategori');
            $table->unique(
                ['tahun', 'bulan', 'is_triwulan', 'is_akumulasi', 'tipe_losses', 'tipe'],
                'rt_losses_unique'
            );
        });
    }
};