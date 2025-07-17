<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttributeValue;

class AttributeValueSeeder extends Seeder
{
    public function run(): void
    {
        $values = [
            
            ['thuoc_tinh_id' => 1, 'gia_tri' => 'S'],
            ['thuoc_tinh_id' => 1, 'gia_tri' => 'M'],
            ['thuoc_tinh_id' => 1, 'gia_tri' => 'L'],
            ['thuoc_tinh_id' => 1, 'gia_tri' => 'XL'],
            ['thuoc_tinh_id' => 1, 'gia_tri' => 'XXL'],

          
            ['thuoc_tinh_id' => 2, 'gia_tri' => 'Đỏ'],
            ['thuoc_tinh_id' => 2, 'gia_tri' => 'Xanh'],
            ['thuoc_tinh_id' => 2, 'gia_tri' => 'Vàng'],
            ['thuoc_tinh_id' => 2, 'gia_tri' => 'Đen'],
            ['thuoc_tinh_id' => 2, 'gia_tri' => 'Trắng'],
            ['thuoc_tinh_id' => 2, 'gia_tri' => 'Hồng'],
            ['thuoc_tinh_id' => 2, 'gia_tri' => 'Tím'],

           
            ['thuoc_tinh_id' => 3, 'gia_tri' => 'Cotton'],
            ['thuoc_tinh_id' => 3, 'gia_tri' => 'Polyester'],
            ['thuoc_tinh_id' => 3, 'gia_tri' => 'Da'],
            ['thuoc_tinh_id' => 3, 'gia_tri' => 'Len'],
            ['thuoc_tinh_id' => 3, 'gia_tri' => 'Nỉ'],

        ];

        foreach ($values as $value) {
            AttributeValue::create($value);
        }
    }
}
