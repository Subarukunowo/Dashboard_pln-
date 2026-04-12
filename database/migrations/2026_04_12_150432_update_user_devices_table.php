<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('user_devices', function (Blueprint $table) {
        $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('ip_address');
        $table->text('reject_reason')->nullable()->after('status');
        $table->timestamp('requested_at')->nullable()->after('reject_reason');
        $table->timestamp('approved_at')->nullable()->after('requested_at');
        $table->foreignId('approved_by')->nullable()->constrained('users')->after('approved_at');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
