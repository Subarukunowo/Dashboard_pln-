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
       Schema::create('rt_fact_produksi', function (Blueprint $table) {
    $table->id();
    $table->smallInteger('tahun');
    $table->tinyInteger('bulan');
    $table->decimal('prod_bruto', 25, 6)->nullable();
    $table->decimal('ps_kit', 25, 6)->nullable();
    $table->decimal('prod_netto', 25, 6)->nullable();
    $table->decimal('loko_transmisi', 25, 6)->nullable();
    $table->decimal('siap_salur_transmisi', 25, 6)->nullable();
    $table->decimal('ps_gi', 25, 6)->nullable();
    $table->decimal('prod_siap_salur', 25, 6)->nullable();
    $table->decimal('interkoneksi', 25, 6)->nullable();
    $table->decimal('loko_distribusi', 25, 6)->nullable();
    $table->decimal('ps_distribusi', 25, 6)->nullable();
    $table->decimal('blkg_padang', 25, 6)->nullable();
    $table->decimal('sembulang', 25, 6)->nullable();
    $table->decimal('buluh', 25, 6)->nullable();
    $table->decimal('p_nguan', 25, 6)->nullable();
    $table->enum('sumber', ['spreadsheet','manual'])->default('spreadsheet');
    $table->timestamps();

    $table->unique(['tahun','bulan']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_rt_fact_produksi');
    }
};
