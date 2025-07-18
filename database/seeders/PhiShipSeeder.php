<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shipping;

class PhiShipSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['tinh_thanh' => 'Hà Nội', 'phi' => 20000],
            ['tinh_thanh' => 'Hồ Chí Minh', 'phi' => 25000],
            ['tinh_thanh' => 'Đà Nẵng', 'phi' => 15000],
            ['tinh_thanh' => 'Hải Phòng', 'phi' => 18000],
            // Thêm tỉnh khác nếu cần
        ];

        foreach ($data as $item) {
            Shipping::create($item); 
        }
    }
}
