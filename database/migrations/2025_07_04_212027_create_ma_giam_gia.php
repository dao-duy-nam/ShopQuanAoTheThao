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
        Schema::create('ma_giam_gias', function (Blueprint $table) {
            $table->id();
            $table->string('ma')->unique(); // Mã (coupon code)
            $table->string('ten')->nullable(); // Tên mã
            $table->enum('loai', ['phan_tram', 'tien'])->default('tien'); // Loại giảm giá

            $table->enum('ap_dung_cho', ['toan_don', 'san_pham'])->default('toan_don'); // Dùng cho đơn hay sản phẩm
            $table->unsignedBigInteger('san_pham_id')->nullable(); // Nếu áp dụng riêng cho sản phẩm

            $table->unsignedInteger('gia_tri'); // Giá trị giảm (VD: 50% hoặc 100000 VND)
            $table->unsignedInteger('gia_tri_don_hang')->nullable(); // Giá trị đơn hàng tối thiểu

            $table->unsignedInteger('so_luong')->default(0); // Số mã còn lại
            $table->unsignedInteger('so_lan_su_dung')->default(0); // Đã dùng
            $table->unsignedInteger('gioi_han')->nullable(); // Số lần mỗi user dùng

            $table->timestamp('ngay_bat_dau')->nullable();
            $table->timestamp('ngay_ket_thuc')->nullable();
            $table->boolean('trang_thai')->default(true); // Còn dùng được?

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('san_pham_id')->references('id')->on('san_phams')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ma_giam_gias');
    }
};
