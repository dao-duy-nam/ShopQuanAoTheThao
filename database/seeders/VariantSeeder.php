<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Variant;
use App\Models\AttributeValue;

class VariantSeeder extends Seeder
{
    public function run(): void
    {
        $variant = Variant::create([
            'san_pham_id' => 1,
            'so_luong' => 10,
            'gia' => 199000,
            'gia_khuyen_mai' => 179000,
            'hinh_anh' => ['variants/demo1.jpg'],
        ]);

        $attributeValueIds = AttributeValue::inRandomOrder()->limit(2)->pluck('id');
        $variant->attributeValues()->attach($attributeValueIds);
    }
}
