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
            $table->string('so_dien_thoai')->nullable()->after('email');
            $table->date('ngay_sinh');
            $table->enum('gioi_tinh', ['nam', 'nu', 'khac']);
            $table->string('anh_dai_dien')->nullable();
            $table->foreignId('vai_tro_id')->nullable()->constrained('vai_tros')->after('so_dien_thoai');
            $table->enum('trang_thai', ['active', 'inactive'])->default('active')->after('vai_tro_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['vai_tro_id']);
            $table->dropColumn(['so_dien_thoai', 'vai_tro_id', 'trang_thai']);
        });
    }
};
