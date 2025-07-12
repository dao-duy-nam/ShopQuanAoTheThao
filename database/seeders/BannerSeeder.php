<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('banners')->insert([
            [
                'tieu_de' => 'Banner Khuyến Mãi Tháng 7',
                'hinh_anh' => 'banners/banner1.jpg',
                'trang_thai' => 'hien',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tieu_de' => 'Banner Mùa Hè Sôi Động',
                'hinh_anh' => 'banners/banner2.jpg',
                'trang_thai' => 'hien',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tieu_de' => 'Banner Sale 50%',
                'hinh_anh' => 'banners/banner3.jpg',
                'trang_thai' => 'an',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
