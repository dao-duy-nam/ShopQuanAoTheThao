<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Variant;
use App\Models\OrderDetail;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $donHang1 = Order::create([
            'ma_don_hang' => 'DH001',
            'user_id' => 5,
            'phuong_thuc_thanh_toan_id' => 1,
            'trang_thai_don_hang' => 'da_giao',
            'trang_thai_thanh_toan' => 'da_thanh_toan',
            'so_tien_thanh_toan' => 100000,
        ]);

        OrderDetail::create([
            'don_hang_id' => $donHang1->id,
            'san_pham_id' => 1,
            'bien_the_id' => null,
            'so_luong' => 1,
            'don_gia' => 100000,
            'tong_tien' => 100000,
        ]);
        $donHang2 = Order::create([
            'ma_don_hang' => 'DH002',
            'user_id' => 5, // sửa theo user_id có thật
            'phuong_thuc_thanh_toan_id' => 2, // ID phương thức phải tồn tại
            'trang_thai_don_hang' => 'cho_xac_nhan',
            'trang_thai_thanh_toan' => 'cho_xu_ly',
            'so_tien_thanh_toan' => 120000,
        ]);

        OrderDetail::create([
            'don_hang_id' => $donHang1->id,
            'san_pham_id' => 1, // đảm bảo tồn tại
            'bien_the_id' => null,
            'so_luong' => 2,
            'don_gia' => 60000,
            'tong_tien' => 120000,
        ]);

        $donHang3 = Order::create([
            'ma_don_hang' => 'DH003',
            'user_id' => 5,
            'phuong_thuc_thanh_toan_id' => 2,
            'trang_thai_don_hang' => 'cho_xac_nhan',
            'trang_thai_thanh_toan' => 'cho_xu_ly',
            'so_tien_thanh_toan' => 150000,
        ]);

        OrderDetail::create([
            'don_hang_id' => $donHang2->id,
            'san_pham_id' => 1,
            'bien_the_id' => null,
            'so_luong' => 3,
            'don_gia' => 50000,
            'tong_tien' => 150000,
        ]);

        $donHang4 = Order::create([
            'ma_don_hang' => 'DH004',
            'user_id' => 5,
            'phuong_thuc_thanh_toan_id' => 2,
            'trang_thai_don_hang' => 'cho_xac_nhan',
            'trang_thai_thanh_toan' => 'cho_xu_ly',
            'so_tien_thanh_toan' => 200000,
        ]);

        OrderDetail::create([
            'don_hang_id' => $donHang3->id,
            'san_pham_id' => 2,
            'bien_the_id' => null,
            'so_luong' => 4,
            'don_gia' => 50000,
            'tong_tien' => 200000,
        ]);
    }
}
