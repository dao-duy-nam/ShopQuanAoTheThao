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
            $table->integer('otp_attempts_forgot')->default(0)->after('remember_token');
            $table->timestamp('otp_locked_until_forgot')->nullable()->after('otp_attempts_forgot');

            $table->integer('otp_attempts_verify')->default(0)->after('otp_locked_until_forgot');
            $table->timestamp('otp_locked_until_verify')->nullable()->after('otp_attempts_verify');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('otp_attempts_forgot');
            $table->dropColumn('otp_locked_until_forgot');
            $table->dropColumn('otp_attempts_verify');
            $table->dropColumn('otp_locked_until_verify');
        });
    }
};
