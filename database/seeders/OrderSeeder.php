<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderDetail;
use App\Models\PaymentMethod;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $products = Product::all();
        $paymentMethods = PaymentMethod::all();

        if ($users->isEmpty() || $products->isEmpty() || $paymentMethods->isEmpty()) {
            $this->command->warn('Cần có dữ liệu sẵn trong bảng users, products, payment_methods trước.');
            return;
        }

        // Tạo đơn hàng trong 6 tháng gần nhất
        for ($i = 0; $i < 6; $i++) {
            $date = Carbon::now()->subMonths($i);

            for ($j = 0; $j < 5; $j++) {
                $user = $users->random();
                $product = $products->random();
                $payment = $paymentMethods->random();

                $so_luong = rand(1, 3);
                $gia = $product->gia_ban ?? rand(100000, 300000);
                $tong_tien = $gia * $so_luong;

                $order = Order::create([
                'ma_don_hang' => 'DH' . strtoupper(uniqid()),
                'user_id' => $user->id,
                'email_nguoi_dat' => $user->email,
                'ten_nguoi_dat' => $user->name,
                'dia_chi_day_du' => '123 Đường ABC, Quận 1',
                'thanh_pho' => 'TP.HCM',
                'phuong_thuc_thanh_toan_id' => $payment->id,
                'trang_thai_don_hang' => 'da_giao',
                'trang_thai_thanh_toan' => 'da_thanh_toan',
                'so_tien_thanh_toan' => 500000,
                'phi_ship' => 20000,
                'ma_giam_gia' => null,
                'so_tien_duoc_giam' => 0,
                'thoi_gian_nhan' => Carbon::now()->addDays(3),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

                // Tạo chi tiết đơn hàng
                OrderDetail::create([
                'don_hang_id' => $order->id,
                'san_pham_id' => $product->id,
                'so_luong' => $so_luong,
                'don_gia' => $gia,
                'tong_tien' => $tong_tien,
            ]);
            }
        }
    }
}
