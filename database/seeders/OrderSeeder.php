<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderDetail;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Đơn hàng đã giao thành công
        $donHang1 = Order::create([
            'ma_don_hang' => 'DH001',
            'user_id' => 2,
            'phuong_thuc_thanh_toan_id' => 1,
            'trang_thai_don_hang' => 'da_giao',
            'trang_thai_thanh_toan' => 'da_thanh_toan',
        ]);

        OrderDetail::create([
            'don_hang_id' => $donHang1->id,
            'san_pham_id' => 1,
            'bien_the_id' => null,
            'so_luong' => 1,
            'don_gia' => 100000,
            'tong_tien' => 100000, // hoặc 'don_gia' * 'so_luong'
        ]);
    }
}
