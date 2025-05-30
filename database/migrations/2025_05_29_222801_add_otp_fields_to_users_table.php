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
        Schema::table('users', function (Blueprint $table) {
            
            $table->integer('otp_attempts')->default(0)->after('otp');

            
            $table->timestamp('otp_locked_until')->nullable()->after('otp_attempts');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('otp_attempts');
            $table->dropColumn('otp_locked_until');
        });
    }
};
