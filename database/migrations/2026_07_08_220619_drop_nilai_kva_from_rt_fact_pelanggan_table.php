<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rt_fact_pelanggan', function (Blueprint $table) {
            $table->dropColumn('nilai_kva');
        });
    }

    public function down(): void
    {
        Schema::table('rt_fact_pelanggan', function (Blueprint $table) {
            $table->decimal('nilai_kva', 25, 6)->nullable();
        });
    }
};