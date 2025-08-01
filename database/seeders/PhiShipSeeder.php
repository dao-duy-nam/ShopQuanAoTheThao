<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shipping;

class PhiShipSeeder extends Seeder
{
    public function run(): void
    {
        $provinces = [
            "Hà Nội",
            "Hải Phòng",
            "Đà Nẵng",
            "Huế",
            "Hồ Chí Minh",
            "Cần Thơ",
            "Cao Bằng",
            "Điện Biên",
            "Lai Châu",
            "Sơn La",
            "Lạng Sơn",
            "Quảng Ninh",
            "Thanh Hóa",
            "Nghệ An",
            "Hà Tĩnh",
            "Tuyên Quang",
            "Lào Cai",
            "Thái Nguyên",
            "Phú Thọ",
            "Bắc Ninh",
            "Hưng Yên",
            "Ninh Bình",
            "Quảng Trị",
            "Quảng Ngãi",
            "Gia Lai",
            "Khánh Hòa",
            "Lâm Đồng",
            "Đắk Lắk",
            "Vĩnh Long",
            "Đồng Tháp",
            "An Giang",
            "Cà Mau",
        ];

        foreach ($provinces as $province) {
            Shipping::create([
                'tinh_thanh' => $province,
                'phi' => rand(15000, 50000),
            ]);
        }
    }
}
