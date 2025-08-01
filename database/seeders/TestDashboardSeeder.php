<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product; // đảm bảo bạn có Model Product
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TestDashboardSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo 1 sản phẩm nếu chưa có để lấy san_pham_id
        $product = Product::first();
        if (!$product) {
            $product = Product::create([
                'ten_san_pham' => 'Áo Thể Thao Giả Lập',
                'mo_ta' => 'Sản phẩm dùng cho dashboard test',
                'gia' => 500000,
                'so_luong' => 100,
            ]);
        }

        // Tạo 2 users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Ngày tháng
        $lastMonthDate = Carbon::now()->subMonth()->startOfMonth()->addDays(2);
        $thisMonthDate = Carbon::now()->startOfMonth()->addDays(2);

        // === ĐƠN HÀNG THÁNG TRƯỚC ===
        for ($i = 0; $i < 3; $i++) {
            $order = Order::create([
                'ma_don_hang' => 'DH' . strtoupper(Str::random(8)),
                'user_id' => $user1->id,
                'email_nguoi_dat' => $user1->email,
                'ten_nguoi_dat' => $user1->name,
                'dia_chi_day_du' => '123 Đường ABC, Quận 1',
                'thanh_pho' => 'TP.HCM',
                'phuong_thuc_thanh_toan_id' => 1,
                'trang_thai_don_hang' => 'da_giao',
                'trang_thai_thanh_toan' => 'da_thanh_toan',
                'so_tien_thanh_toan' => 500000,
                'phi_ship' => 20000,
                'ma_giam_gia' => null,
                'so_tien_duoc_giam' => 0,
                'thoi_gian_nhan' => Carbon::now()->addDays(3),
                'created_at' => $lastMonthDate->copy()->addDays($i),
                'updated_at' => $lastMonthDate->copy()->addDays($i),
            ]);

            $soLuong = 2;
            $donGia = 500000;

            OrderDetail::create([
                'don_hang_id' => $order->id,
                'san_pham_id' => $product->id,
                'so_luong' => $soLuong,
                'don_gia' => $donGia,
                'tong_tien' => $soLuong * $donGia,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ]);
        }

        // === ĐƠN HÀNG THÁNG NÀY ===
        for ($i = 0; $i < 5; $i++) {
            $order = Order::create([
                'ma_don_hang' => 'DH' . strtoupper(Str::random(8)),
                'user_id' => $user2->id,
                'email_nguoi_dat' => $user2->email,
                'ten_nguoi_dat' => $user2->name,
                'dia_chi_day_du' => '456 Đường XYZ, Quận 3',
                'thanh_pho' => 'Hà Nội',
                'phuong_thuc_thanh_toan_id' => 1,
                'trang_thai_don_hang' => 'da_giao',
                'trang_thai_thanh_toan' => 'da_thanh_toan',
                'so_tien_thanh_toan' => 1500000,
                'phi_ship' => 30000,
                'ma_giam_gia' => null,
                'so_tien_duoc_giam' => 0,
                'thoi_gian_nhan' => Carbon::now()->addDays(5),
                'created_at' => $thisMonthDate->copy()->addDays($i),
                'updated_at' => $thisMonthDate->copy()->addDays($i),
            ]);

            $soLuong = 3;
            $donGia = 500000;

            OrderDetail::create([
                'don_hang_id' => $order->id,
                'san_pham_id' => $product->id,
                'so_luong' => $soLuong,
                'don_gia' => $donGia,
                'tong_tien' => $soLuong * $donGia,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ]);
        }
    }
}
