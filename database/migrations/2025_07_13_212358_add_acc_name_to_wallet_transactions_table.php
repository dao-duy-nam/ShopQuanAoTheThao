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
        Schema::table('wallet_transactions', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->string('acc_name')->nullable()->after('bank_account');
        });
    }

    public function down()
    {
        Schema::table('wallet_transactions', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->dropColumn('acc_name');
        });
    }
};
