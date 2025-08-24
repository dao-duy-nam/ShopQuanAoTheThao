<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Variant;
use App\Models\Attribute;
use Faker\Factory as Faker;
use App\Models\AttributeValue;
use Illuminate\Database\Seeder;

class VariantSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $imageNames = [
            '5bff93b7-027c-445a-9057-aca0e651044f.jpg',
            '7f197c88-4f46-430e-bdb1-231bd11b5860.jpg',
            '11af299-5824-4a01-b103-8107150cd7ed.jpg',
            '39d8343e-8503-4c58-8826-2d436036b0c7.jpg',
            '324c3af8-8450-49c7-8956-ada5e7755e39.jpg',
            '473ebc23-28ac-4c44-bc6e-963289106996.jpg',
            '677bd66c-9aaa-4709-a87a-9c6d5a5ae640.jpg',
            '811baaf5-6d80-4313-aa05-7e81070facb6.jpg',
            'a76e7422-0422-4b40-a952-9e393bf4a545.jpg',
            'ac4a56f1-52d6-4e97-82fa-431989184c60.jpg',
            'e76af41b-3bf2-4903-98d8-0dc31946163a.jpg',
            'e192ebf7-c10c-451e-9a38-173ef7775330.jpg',
            'H35Yb7WRFzk8M55DlylBWbmGRVlVRCrjkSxacyxX.jpg',
            'Lưu=fl.jpg',
            'OQfL7OuDlYTdnbet7x9hT3LB53ENZBA7JB5c8.jpg',
            'vzGbTz5wlnRxRaVmXITnYlokuTF644bFP5t3zv1.jpg',
            'xTHoASDfqwGw6h3gIfRES2NNLGYbVAzo7ysyzJ5O.jpg',
        ];

        $products = Product::all();
        $attributes = Attribute::all();

        if ($products->isEmpty() || $attributes->isEmpty()) {
            $this->command->warn('Không có sản phẩm hoặc thuộc tính nào trong database.');
            return;
        }

        foreach ($products as $product) {
            $variantCount = max(3, rand(3, 5));
            $usedCombinations = [];

            for ($i = 0; $i < $variantCount; $i++) {
                $attempt = 0;

                do {
                    $attributeValueIds = collect();
                    foreach ($attributes as $attribute) {
                        $value = $attribute->values()->inRandomOrder()->first();
                        if ($value) {
                            $attributeValueIds->push($value->id);
                        }
                    }

                    $attributeValueIds = $attributeValueIds->sort()->values(); // sắp xếp để so trùng
                    $comboKey = $attributeValueIds->implode('-');
                    $attempt++;
                } while (in_array($comboKey, $usedCombinations) && $attempt < 10);

                if (in_array($comboKey, $usedCombinations)) {
                    $this->command->warn("Bỏ qua biến thể trùng cho sản phẩm ID {$product->id}");
                    continue;
                }

                $usedCombinations[] = $comboKey;

                $gia = rand(100000, 300000);
                $hasDiscount = rand(0, 1);
                $giaKhuyenMai = $hasDiscount ? rand(80000, $gia - 10000) : null;

                $variant = Variant::create([
                    'san_pham_id' => $product->id,
                    'so_luong' => rand(5, 50),
                    'gia' => $gia,
                    'gia_khuyen_mai' => $giaKhuyenMai,
                    'hinh_anh' => [
                        'variants/' . $faker->randomElement($imageNames)
                    ],
                ]);

                $variant->attributeValues()->attach($attributeValueIds);
            }
        }
    }
}
