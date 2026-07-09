<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE rt_fact_jam_nyala MODIFY golongan_kode VARCHAR(40) NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE rt_fact_jam_nyala MODIFY golongan_kode VARCHAR(10) NOT NULL');
    }
};