<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cập nhật ENUM cho trạng thái đơn hàng
        DB::statement("ALTER TABLE don_hangs MODIFY trang_thai_don_hang ENUM(
            'cho_xac_nhan',
            'dang_chuan_bi',
            'dang_van_chuyen',
            'da_giao',
            'da_nhan',
            'yeu_cau_tra_hang',
            'xac_nhan_tra_hang',
            'tu_choi_tra_hang',
            'tra_hang_thanh_cong',
            'yeu_cau_huy_hang',
            'da_huy'
        )");

        // Cập nhật ENUM cho trạng thái thanh toán
        DB::statement("ALTER TABLE don_hangs MODIFY trang_thai_thanh_toan ENUM(
            'cho_xu_ly',
            'da_thanh_toan',
            'that_bai',
            'cho_hoan_tien',
            'hoan_tien',
            'da_huy'
        )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback về ENUM cũ (nếu cần)
        DB::statement("ALTER TABLE don_hangs MODIFY trang_thai_don_hang ENUM(
            'cho_xac_nhan',
            'dang_chuan_bi',
            'dang_van_chuyen',
            'da_giao',
            'da_huy',
            'tra_hang'
        )");

        DB::statement("ALTER TABLE don_hangs MODIFY trang_thai_thanh_toan ENUM(
            'cho_xu_ly',
            'da_thanh_toan',
            'that_bai',
            'hoan_tien',
            'da_huy'
        )");
    }
};