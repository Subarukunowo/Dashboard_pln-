<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rt_fact_p2tl', function (Blueprint $table) {
            $table->integer('jumlah_pemeriksaan')->unsigned()->default(0)->after('tahun');
            $table->decimal('temuan_kwh', 25, 6)->default(0)->after('jumlah_temuan');
        });
    }

    public function down(): void
    {
        Schema::table('rt_fact_p2tl', function (Blueprint $table) {
            $table->dropColumn(['jumlah_pemeriksaan', 'temuan_kwh']);
        });
    }
};