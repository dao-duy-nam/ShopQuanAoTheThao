<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('don_hangs', function (Blueprint $table) {
            $table->string('email_nguoi_dat')->nullable()->after('user_id');
            $table->string('sdt_nguoi_dat')->nullable()->after('email_nguoi_dat');
        });
    }

    public function down(): void
    {
        Schema::table('don_hangs', function (Blueprint $table) {
            $table->dropColumn(['email_nguoi_dat', 'sdt_nguoi_dat']);
        });
    }
};
