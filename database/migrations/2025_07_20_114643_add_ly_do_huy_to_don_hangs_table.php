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
        Schema::table('don_hangs', function (Blueprint $table) {
            $table->string('ly_do_huy')->nullable()->after('trang_thai_don_hang');
            $table->string('ly_do_tra_hang')->nullable()->after('trang_thai_don_hang');
            $table->string('ly_do_tu_choi_tra_hang')->nullable()->after('trang_thai_don_hang');
            $table->dateTime('thoi_gian_nhan')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('don_hangs', function (Blueprint $table) {
            $table->dropColumn('ly_do_huy');
        });
    }
};
