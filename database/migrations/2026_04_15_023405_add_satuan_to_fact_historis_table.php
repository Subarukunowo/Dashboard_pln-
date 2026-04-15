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
    Schema::table('fact_historis', function (Blueprint $table) {
        // Menambahkan kolom satuan setelah kolom nilai
        $table->string('satuan', 20)->nullable()->after('nilai');
    });
}

public function down(): void
{
    Schema::table('fact_historis', function (Blueprint $table) {
        $table->dropColumn('satuan');
    });
}
};
