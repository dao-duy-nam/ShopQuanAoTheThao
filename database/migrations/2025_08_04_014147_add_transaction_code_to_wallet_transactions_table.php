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
    Schema::table('wallet_transactions', function (Blueprint $table) {
        $table->string('transaction_code')->unique()->after('wallet_id');
    });
}

public function down()
{
    Schema::table('wallet_transactions', function (Blueprint $table) {
        $table->dropColumn('transaction_code');
    });
}

};
