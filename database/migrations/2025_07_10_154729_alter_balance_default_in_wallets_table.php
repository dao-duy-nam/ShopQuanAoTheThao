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
        \DB::statement('UPDATE wallets SET balance = 0 WHERE balance IS NULL');
        Schema::table('wallets', function (Blueprint $table) {
            $table->decimal('balance', 15, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->decimal('balance', 15, 2)->nullable()->default(null)->change();
        });
    }
};
