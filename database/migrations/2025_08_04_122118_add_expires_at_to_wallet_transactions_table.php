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
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('description')->comment('Thời gian hết hạn giao dịch');
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });
    }
};
