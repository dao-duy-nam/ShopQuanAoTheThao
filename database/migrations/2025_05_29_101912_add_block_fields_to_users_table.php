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
        Schema::table('users', function (Blueprint $table) {
            //
             $table->text('ly_do_block')->nullable()->after('trang_thai');
        $table->timestamp('block_den_ngay')->nullable()->after('ly_do_block');
        $table->enum('kieu_block', ['1_ngay', '7_ngay', 'vinh_vien'])->nullable()->after('block_den_ngay');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn(['ly_do_block', 'block_den_ngay', 'kieu_block']);
        });
    }
};
