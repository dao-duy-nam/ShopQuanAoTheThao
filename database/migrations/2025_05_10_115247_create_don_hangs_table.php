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
        Schema::create('don_hangs', function (Blueprint $table) {
            $table->id();
            $table->string('ma_don_hang')->unique();
            $table->foreignId('nguoi_dung_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('phuong_thuc_thanh_toan_id')->constrained('phuong_thuc_thanh_toans');
            $table->enum('trang_thai_don_hang', ['cho_xac_nhan', 'dang_chuan_bi', 'dang_van_chuyen', 'da_giao', 'da_huy', 'tra_hang']);
            $table->enum('trang_thai_thanh_toan', ['cho_xu_ly', 'da_thanh_toan', 'that_bai', 'hoan_tien', 'da_huy']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('don_hangs');
    }
};
