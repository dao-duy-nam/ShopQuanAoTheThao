<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Variant;
use App\Models\AttributeValue;
use App\Models\Product; 
class VariantSeeder extends Seeder
{
    public function run(): void
    {
        $productIds = Product::pluck('id');
        if ($productIds->isEmpty()) {
            $this->command->warn('Không có sản phẩm nào trong bảng products.');
            return;
        }
        for ($i = 1; $i <= 5; $i++) {
            $variant = Variant::create([
                'san_pham_id' => $productIds->random(), 
                'so_luong' => rand(5, 50),
                'gia' => rand(100000, 300000),
                'gia_khuyen_mai' => rand(80000, 250000),
                'hinh_anh' => ['variants/demo' . $i . '.jpg'],
            ]);

            $attributeValueIds = AttributeValue::inRandomOrder()->limit(2)->pluck('id');
            $variant->attributeValues()->attach($attributeValueIds);
        }
    }
}
