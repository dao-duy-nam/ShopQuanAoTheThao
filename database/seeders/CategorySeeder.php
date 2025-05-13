<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::create([
            'ten' => 'Thời trang nam',
            'mo_ta' => 'Các sản phẩm thời trang dành cho nam giới',
        ]);

        Category::create([
            'ten' => 'Thời trang nữ',
            'mo_ta' => 'Các sản phẩm thời trang dành cho nữ giới',
        ]);
    }
}
