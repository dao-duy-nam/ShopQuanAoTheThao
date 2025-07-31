<?php

namespace Database\Seeders;

// database/seeders/ChiTietDonHangSeeder.php

use Illuminate\Database\Seeder;
use App\Models\ChiTietDonHang;
use App\Models\DonHang;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\SanPham;

class ChiTietDonHangSeeder extends Seeder
{
    public function run(): void
    {
        $donHangs = Order::all();
        $sanPhamIds = Product::pluck('id')->toArray();
        foreach ($donHangs as $order) {
            OrderDetail::create([
                'don_hang_id' => $order->id,
                'san_pham_id' => $sanPhamIds[array_rand($sanPhamIds)],
                'so_luong' => rand(1, 3),
            ]);
        }
    }
}

