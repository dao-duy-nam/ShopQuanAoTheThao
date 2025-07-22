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
                'tieu_de' => 'Khuyến mãi độc quyền tháng 7',
                'hinh_anh' => 'banners/1d264c988391a6b743cfbd299b381170.jpg',
                'trang_thai' => 'hien',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tieu_de' => 'Chào hè – Ưu đãi khủng',
                'hinh_anh' => 'banners/1f0865ae231cd9efe9bdd7147a92d4a1.jpg',
                'trang_thai' => 'hien',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tieu_de' => 'Sale Sốc 50% – Duy nhất hôm nay',
                'hinh_anh' => 'banners/214d3ec8be4c2084e2dcaecb66734fd8.jpg',
                'trang_thai' => 'an',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
