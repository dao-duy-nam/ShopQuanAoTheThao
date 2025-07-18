<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('don_hangs', function (Blueprint $table) {
            $table->string('ma_giam_gia')->nullable();
        });

        Schema::table('chi_tiet_don_hangs', function (Blueprint $table) {
            $table->string('ma_giam_gia')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('don_hangs', function (Blueprint $table) {
            $table->dropColumn('ma_giam_gia');
        });

        Schema::table('chi_tiet_don_hangs', function (Blueprint $table) {
            $table->dropColumn('ma_giam_gia');
        });
    }
};
