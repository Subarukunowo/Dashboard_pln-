<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('rt_fact_hjr', function (Blueprint $table) {
    $table->id();
    $table->smallInteger('tahun');
    $table->tinyInteger('bulan');
    $table->boolean('is_triwulan')->default(false);
    $table->boolean('is_akumulasi')->default(false);
    $table->enum('tipe', ['target', 'realisasi']);
    $table->string('tarif_kode', 50);
    $table->enum('tegangan', ['TR', 'TM', 'TT']);
    $table->decimal('nilai_rpkwh', 25, 6)->default(0);
    $table->enum('sumber', ['spreadsheet', 'manual'])->default('spreadsheet');
    $table->timestamps();

    $table->unique(['tahun', 'bulan', 'is_triwulan', 'is_akumulasi', 'tipe', 'tarif_kode'], 'rt_hjr_unique');
    $table->index(['tahun', 'bulan']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rt_fact_hjr');
    }
};
