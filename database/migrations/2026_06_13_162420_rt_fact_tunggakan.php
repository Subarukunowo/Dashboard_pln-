<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rt_fact_tunggakan', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('tahun');
            $table->tinyInteger('bulan');
            $table->decimal('pal_berjalan', 25, 6)->default(0);
            $table->decimal('pal_tunggakan', 25, 6)->default(0);
            $table->decimal('ts_berjalan', 25, 6)->default(0);
            $table->decimal('ts_tunggakan', 25, 6)->default(0);
            $table->decimal('jumlah_tunggakan', 25, 6)->default(0);
            $table->decimal('pendapatan', 25, 6)->default(0);
            $table->decimal('ratio_tunggakan_pct', 10, 6)->default(0);
            $table->enum('sumber', ['spreadsheet','manual'])->default('spreadsheet');
            $table->timestamps();

            $table->unique(['tahun', 'bulan']);
            $table->index(['tahun', 'bulan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rt_fact_tunggakan');
    }
};