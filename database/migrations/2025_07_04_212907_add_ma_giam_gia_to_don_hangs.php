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
            $table->foreignId('ma_giam_gia_id')->nullable()->constrained('ma_giam_gias')->nullOnDelete();
            $table->decimal('so_tien_duoc_giam', 10, 2)->default(0)->after('ma_giam_gia_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('don_hangs', function (Blueprint $table) {
            $table->dropForeign(['ma_giam_gia_id']);
            $table->dropColumn(['ma_giam_gia_id', 'so_tien_duoc_giam']);
        });
    }
};
