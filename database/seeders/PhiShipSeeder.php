<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shipping;

class PhiShipSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // Miền Bắc
            ['tinh_thanh' => 'Hà Nội', 'phi' => 20000],
            ['tinh_thanh' => 'Bắc Giang', 'phi' => 21000],
            ['tinh_thanh' => 'Bắc Kạn', 'phi' => 22000],
            ['tinh_thanh' => 'Bắc Ninh', 'phi' => 20000],
            ['tinh_thanh' => 'Cao Bằng', 'phi' => 22000],
            ['tinh_thanh' => 'Điện Biên', 'phi' => 23000],
            ['tinh_thanh' => 'Hà Giang', 'phi' => 23000],
            ['tinh_thanh' => 'Hà Nam', 'phi' => 20000],
            ['tinh_thanh' => 'Hải Dương', 'phi' => 20000],
            ['tinh_thanh' => 'Hải Phòng', 'phi' => 20000],
            ['tinh_thanh' => 'Hòa Bình', 'phi' => 21000],
            ['tinh_thanh' => 'Hưng Yên', 'phi' => 20000],
            ['tinh_thanh' => 'Lai Châu', 'phi' => 23000],
            ['tinh_thanh' => 'Lạng Sơn', 'phi' => 22000],
            ['tinh_thanh' => 'Lào Cai', 'phi' => 23000],
            ['tinh_thanh' => 'Nam Định', 'phi' => 20000],
            ['tinh_thanh' => 'Ninh Bình', 'phi' => 20000],
            ['tinh_thanh' => 'Phú Thọ', 'phi' => 21000],
            ['tinh_thanh' => 'Quảng Ninh', 'phi' => 21000],
            ['tinh_thanh' => 'Sơn La', 'phi' => 22000],
            ['tinh_thanh' => 'Thái Bình', 'phi' => 20000],
            ['tinh_thanh' => 'Thái Nguyên', 'phi' => 21000],
            ['tinh_thanh' => 'Tuyên Quang', 'phi' => 22000],
            ['tinh_thanh' => 'Vĩnh Phúc', 'phi' => 20000],
            ['tinh_thanh' => 'Yên Bái', 'phi' => 22000],

            // Miền Trung
            ['tinh_thanh' => 'Đà Nẵng', 'phi' => 18000],
            ['tinh_thanh' => 'Thanh Hóa', 'phi' => 21000],
            ['tinh_thanh' => 'Nghệ An', 'phi' => 21000],
            ['tinh_thanh' => 'Hà Tĩnh', 'phi' => 21000],
            ['tinh_thanh' => 'Quảng Bình', 'phi' => 22000],
            ['tinh_thanh' => 'Quảng Trị', 'phi' => 22000],
            ['tinh_thanh' => 'Thừa Thiên Huế', 'phi' => 22000],
            ['tinh_thanh' => 'Quảng Nam', 'phi' => 22000],
            ['tinh_thanh' => 'Quảng Ngãi', 'phi' => 22000],
            ['tinh_thanh' => 'Bình Định', 'phi' => 22000],
            ['tinh_thanh' => 'Phú Yên', 'phi' => 22000],
            ['tinh_thanh' => 'Khánh Hòa', 'phi' => 22000],
            ['tinh_thanh' => 'Ninh Thuận', 'phi' => 22000],
            ['tinh_thanh' => 'Bình Thuận', 'phi' => 22000],
            ['tinh_thanh' => 'Kon Tum', 'phi' => 23000],
            ['tinh_thanh' => 'Gia Lai', 'phi' => 23000],
            ['tinh_thanh' => 'Đắk Lắk', 'phi' => 23000],
            ['tinh_thanh' => 'Đắk Nông', 'phi' => 23000],
            ['tinh_thanh' => 'Lâm Đồng', 'phi' => 23000],

            // Miền Nam
            ['tinh_thanh' => 'TP. Hồ Chí Minh', 'phi' => 25000],
            ['tinh_thanh' => 'Bà Rịa - Vũng Tàu', 'phi' => 24000],
            ['tinh_thanh' => 'Bình Dương', 'phi' => 25000],
            ['tinh_thanh' => 'Bình Phước', 'phi' => 25000],
            ['tinh_thanh' => 'Tây Ninh', 'phi' => 25000],
            ['tinh_thanh' => 'Long An', 'phi' => 25000],
            ['tinh_thanh' => 'Tiền Giang', 'phi' => 25000],
            ['tinh_thanh' => 'Bến Tre', 'phi' => 25000],
            ['tinh_thanh' => 'Trà Vinh', 'phi' => 25000],
            ['tinh_thanh' => 'Vĩnh Long', 'phi' => 25000],
            ['tinh_thanh' => 'Đồng Tháp', 'phi' => 25000],
            ['tinh_thanh' => 'An Giang', 'phi' => 25000],
            ['tinh_thanh' => 'Hậu Giang', 'phi' => 25000],
            ['tinh_thanh' => 'Kiên Giang', 'phi' => 25000],
            ['tinh_thanh' => 'Sóc Trăng', 'phi' => 25000],
            ['tinh_thanh' => 'Bạc Liêu', 'phi' => 25000],
            ['tinh_thanh' => 'Cà Mau', 'phi' => 25000],
            ['tinh_thanh' => 'Cần Thơ', 'phi' => 24000],
        ];

        foreach ($data as $item) {
            Shipping::create($item);
        }
    }
}
