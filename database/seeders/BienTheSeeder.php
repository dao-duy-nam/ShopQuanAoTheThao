<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BienThe;
use App\Models\Product;


class BienTheSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();

        foreach ($products as $sanPham) {
            BienThe::create([
                'san_pham_id' => $sanPham->id,
                'kich_co_id' => 1, 
                'mau_sac_id' => 1,
                'gia' => rand(100000, 500000),
                'so_luong' => rand(10, 50),
            ]);
        }
    }
}
