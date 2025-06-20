<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttributeValue;

class AttributeValueSeeder extends Seeder
{
    public function run(): void
    {
        $values = [
            ['thuoc_tinh_id' => 1, 'gia_tri' => 'M'],
            ['thuoc_tinh_id' => 1, 'gia_tri' => 'L'],
            ['thuoc_tinh_id' => 2, 'gia_tri' => 'Đỏ'],
            ['thuoc_tinh_id' => 2, 'gia_tri' => 'Xanh'],
        ];

        foreach ($values as $value) {
            AttributeValue::create($value);
        }
    }
}
