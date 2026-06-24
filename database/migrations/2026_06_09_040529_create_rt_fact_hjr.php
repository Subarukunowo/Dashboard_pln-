<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rt_fact_hjr', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('tahun');
            $table->tinyInteger('bulan');
            $table->boolean('is_akumulasi')->default(0);
            $table->enum('tipe', ['target', 'realisasi']);
            $table->string('tarif_kode', 50);
            $table->decimal('nilai_rpkwh', 25, 6)->default(0);
            $table->enum('sumber', ['spreadsheet', 'manual'])->default('spreadsheet');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rt_fact_hjr');
    }
};