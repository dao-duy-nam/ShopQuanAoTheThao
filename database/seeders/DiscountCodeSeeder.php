<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\DiscountCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DiscountCodeSeeder extends Seeder
{
    public function run(): void
    {
        if (Product::count() === 0) {
            $this->command->warn('Chưa có sản phẩm nào trong bảng products. Vui lòng seed hoặc thêm sản phẩm trước khi seed mã giảm giá.');
            return;
        }

        $products = Product::pluck('id')->toArray();

        for ($i = 1; $i <= 20; $i++) {
            $apDungCho = fake()->randomElement(['toan_don', 'san_pham']);

            DiscountCode::create([
                'ma' => strtoupper('GG' . Str::random(5)),
                'ten' => 'Mã giảm giá ' . $i,
                'loai' => fake()->randomElement(['phan_tram', 'tien']),
                'ap_dung_cho' => $apDungCho,
                'san_pham_id' => $apDungCho === 'san_pham' ? fake()->randomElement($products) : null,
                'gia_tri' => fake()->numberBetween(5, 50),
                'gia_tri_don_hang' => fake()->optional()->numberBetween(100000, 500000),
                'mo_ta' => fake()->randomElement([
                    'Áp dụng cho toàn bộ sản phẩm trong cửa hàng.',
                    'Mã giảm giá đặc biệt dành cho khách hàng thân thiết.',
                    'Áp dụng cho một số sản phẩm thể thao được chọn.',
                    'Giảm giá hấp dẫn cho dịp lễ đặc biệt.'
                ]),
                // 'so_luong' => fake()->numberBetween(10, 100),
                'so_lan_su_dung' => 0,
                'gioi_han' => fake()->optional()->numberBetween(1, 10),
                'ngay_bat_dau' => now(),
                'ngay_ket_thuc' => now()->addDays(fake()->numberBetween(5, 30)),
                'trang_thai' => true,
            ]);
        }
    }
}
