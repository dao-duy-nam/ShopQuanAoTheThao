<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('don_hangs', function (Blueprint $table) {
            $table->json('hinh_anh_tra_hang')->nullable()->after('ly_do_tra_hang');
        });
    }

    public function down(): void
    {
        Schema::table('don_hangs', function (Blueprint $table) {
            $table->dropColumn('hinh_anh_tra_hang');
        });
    }
};
