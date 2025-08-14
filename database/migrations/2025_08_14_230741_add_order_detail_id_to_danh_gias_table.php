<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('danh_gias', function (Blueprint $table) {
            $table->foreignId('chi_tiet_don_hang_id')
                  ->nullable()
                  ->constrained('chi_tiet_don_hangs')
                  ->onDelete('cascade')
                  ->after('user_id'); 
        });
    }

    public function down(): void
    {
        Schema::table('danh_gias', function (Blueprint $table) {
            $table->dropForeign(['chi_tiet_don_hang_id']);
            $table->dropColumn('chi_tiet_don_hang_id');
        });
    }
};
