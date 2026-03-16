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
       Schema::create('dim_versi_laporan', function (Blueprint $table) {
    $table->id('id_versi');
    $table->string('nama_versi', 100); // Contoh: POJK-Non ISAK 112
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dim_versi_laporan');
    }
};
