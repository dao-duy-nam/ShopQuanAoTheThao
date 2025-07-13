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
        $products = Product::all();

        if ($products->isEmpty()) {
            $this->command->warn('Không có sản phẩm nào trong bảng products.');
            return;
        }

        foreach ($products as $product) {
            $variantCount = rand(1, 3);
            $usedCombinations = [];

            for ($i = 1; $i <= $variantCount; $i++) {
                $attempt = 0;
                do {
                    $attributeValueIds = AttributeValue::inRandomOrder()->limit(2)->pluck('id')->sort()->values();
                    $comboKey = $attributeValueIds->implode('-');
                    $attempt++;
                } while (in_array($comboKey, $usedCombinations) && $attempt < 10);
                if (in_array($comboKey, $usedCombinations)) {
                    $this->command->warn("Bỏ qua biến thể trùng cho sản phẩm ID {$product->id}");
                    continue;
                }
                $usedCombinations[] = $comboKey;
                $variant = Variant::create([
                    'san_pham_id' => $product->id,
                    'so_luong' => rand(5, 50),
                    'gia' => rand(100000, 300000),
                    'gia_khuyen_mai' => rand(80000, 250000),
                    'hinh_anh' => ['variants/demo' . rand(1, 5) . '.jpg'],
                ]);

                $variant->attributeValues()->attach($attributeValueIds);
            }
        }
    }
}
