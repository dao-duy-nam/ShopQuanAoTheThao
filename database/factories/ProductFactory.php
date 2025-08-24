<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Variant;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $category = Category::query()->inRandomOrder()->first() ?? Category::factory()->create();

        $usageByCategory = [
            'Áo thun thể thao nam' => 'phù hợp chạy bộ và tập gym hằng ngày',
            'Áo thun thể thao nữ' => 'thoải mái cho yoga, pilates và cardio',
            'Áo khoác thể thao' => 'thuận tiện khi tập luyện ngoài trời và di chuyển',
            'Bộ đồ thể thao nam' => 'phù hợp luyện tập và mặc thường ngày',
            'Bộ đồ thể thao nữ' => 'thích hợp phòng gym và yoga',
            'Quần short thể thao nam' => 'thích hợp đá bóng, chạy bộ và bài tập cường độ cao',
            'Quần jogger nam' => 'phù hợp tập luyện và mặc thường ngày',
            'Quần legging thể thao nữ' => 'thoải mái khi tập gym, yoga và chạy bộ',
            'Áo bóng đá' => 'thích hợp thi đấu và tập luyện',
            'Áo bóng rổ' => 'hỗ trợ vận động linh hoạt trên sân',
            'Áo tập gym nam' => 'hạn chế bám mồ hôi khi vận động cường độ cao',
            'Áo bra thể thao nữ' => 'nâng đỡ tốt, thấm hút nhanh khi chạy bộ và tập luyện',
        ];

        $priceRanges = [
            'Áo thun thể thao nam' => [199000, 399000],
            'Áo thun thể thao nữ' => [189000, 379000],
            'Áo khoác thể thao' => [399000, 799000],
            'Bộ đồ thể thao nam' => [499000, 899000],
            'Bộ đồ thể thao nữ' => [499000, 899000],
            'Quần short thể thao nam' => [199000, 349000],
            'Quần jogger nam' => [299000, 499000],
            'Quần legging thể thao nữ' => [249000, 399000],
            'Áo bóng đá' => [249000, 499000],
            'Áo bóng rổ' => [249000, 499000],
            'Áo tập gym nam' => [199000, 349000],
            'Áo bra thể thao nữ' => [149000, 299000],
        ];

        $categoryName = $category->ten;

        $detailNameTemplates = [
            'Áo thun thể thao nam' => ['Áo thun nam cổ tròn', 'Áo thun nam cổ tim', 'Áo thun nam tay ngắn'],
            'Áo thun thể thao nữ' => ['Áo thun nữ cổ tròn', 'Áo thun nữ tay lỡ', 'Áo thun nữ dáng rộng'],
            'Áo khoác thể thao' => ['Áo khoác gió', 'Áo khoác chống nắng', 'Áo khoác 2 lớp'],
            'Bộ đồ thể thao nam' => ['Bộ thể thao nam 2 món', 'Bộ thể thao nam 3 món'],
            'Bộ đồ thể thao nữ' => ['Bộ thể thao nữ 2 món', 'Bộ thể thao nữ 3 món'],
            'Quần short thể thao nam' => ['Quần short nam 2 lớp', 'Quần short nam chạy bộ'],
            'Quần jogger nam' => ['Quần jogger nam bo gấu', 'Quần jogger nam thể thao'],
            'Quần legging thể thao nữ' => ['Quần legging nữ tập gym', 'Quần legging nữ yoga'],
            'Áo bóng đá' => ['Áo bóng đá nam CLB', 'Áo bóng đá nam đội tuyển'],
            'Áo bóng rổ' => ['Áo bóng rổ nam NBA', 'Áo bóng rổ nam không tay'],
            'Áo tập gym nam' => ['Áo tanktop nam tập gym', 'Áo ba lỗ nam gym'],
            'Áo bra thể thao nữ' => ['Áo bra nữ tập gym', 'Áo bra nữ chạy bộ'],
        ];

        $template = $detailNameTemplates[$categoryName] ?? null;
        $detailName = $template ? $this->faker->randomElement($template) : 'Sản phẩm thể thao';
        $usage = $usageByCategory[$categoryName] ?? 'phù hợp cho các hoạt động thể thao và mặc hằng ngày';

        [$minPrice, $maxPrice] = $priceRanges[$categoryName] ?? [199000, 599000];
        $basePrice = $this->faker->numberBetween($minPrice, $maxPrice);
        $discountPrice = $this->faker->boolean(40)
            ? max($minPrice, $basePrice - $this->faker->numberBetween(20000, 80000))
            : null;

        return [
            'ten' => $detailName,
            'so_luong' => 0,
            'mo_ta' => 'Chất liệu co giãn 4 chiều, thấm hút mồ hôi tốt, nhanh khô; ' . $usage . '.',
            'hinh_anh' => $this->randomProductImage(),
            'danh_muc_id' => $category->id,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Product $product) {
            $sizeAttribute = Attribute::where('ten', 'Kích cỡ')->first();
            $colorAttribute = Attribute::where('ten', 'Màu sắc')->first();

            $sizes = $sizeAttribute ? AttributeValue::where('thuoc_tinh_id', $sizeAttribute->id)->get() : collect();
            $colors = $colorAttribute ? AttributeValue::where('thuoc_tinh_id', $colorAttribute->id)->get() : collect();

            $selectedSizes = $sizes->count() > 0 ? $sizes->shuffle()->take(min($sizes->count(), $this->faker->numberBetween(3, 5))) : collect();
            $selectedColors = $colors->count() > 0 ? $colors->shuffle()->take(min($colors->count(), $this->faker->numberBetween(2, 4))) : collect();

            $totalQuantity = 0;

            if ($selectedSizes->isNotEmpty() && $selectedColors->isNotEmpty()) {
                foreach ($selectedSizes as $size) {
                    foreach ($selectedColors as $color) {
                        $categoryName = optional($product->category)->ten;
                        [$minP, $maxP] = $this->getPriceBoundsForCategory($categoryName);
                        $baseVariant = $this->faker->numberBetween($minP, $maxP);
                        $variantPrice = max(10000, ($baseVariant + $this->faker->numberBetween(-20, 30)) * 1000);
                        $variantDiscount = $this->faker->boolean(35)
                            ? max(10000, ($baseVariant - $this->faker->numberBetween(15, 60)) * 1000)
                            : null;

                        $qty = $this->faker->numberBetween(5, 30);
                        $totalQuantity += $qty;

                        $variant = Variant::create([
                            'san_pham_id' => $product->id,
                            'so_luong' => $qty,
                            'so_luong_da_ban' => 0,
                            'gia' => $variantPrice,
                            'gia_khuyen_mai' => $variantDiscount,
                            'hinh_anh' => $this->randomVariantImage(), // ✅ chỉ 1 ảnh
                        ]);

                        $variant->attributeValues()->syncWithoutDetaching([$size->id, $color->id]);
                    }
                }
            }

            $product->update(['so_luong' => $totalQuantity]);
        });
    }

    private function getPriceBoundsForCategory(?string $categoryName): array
    {
        $priceRanges = [
            'Áo thun thể thao nam' => [199, 399],
            'Áo thun thể thao nữ' => [189, 379],
            'Áo khoác thể thao' => [399, 799],
            'Bộ đồ thể thao nam' => [499, 899],
            'Bộ đồ thể thao nữ' => [499, 899],
            'Quần short thể thao nam' => [199, 349],
            'Quần jogger nam' => [299, 499],
            'Quần legging thể thao nữ' => [249, 399],
            'Áo bóng đá' => [249, 499],
            'Áo bóng rổ' => [249, 499],
            'Áo tập gym nam' => [199, 349],
            'Áo bra thể thao nữ' => [149, 299],
        ];
        return $priceRanges[$categoryName] ?? [199, 599];
    }

    private function getAvailableProductImages(): array
    {
        $dir = storage_path('app/public/products');
        if (!is_dir($dir)) {
            return [];
        }
        $files = glob($dir . DIRECTORY_SEPARATOR . '*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE) ?: [];
        return array_values(array_map('basename', $files));
    }

    private function randomProductImage(): ?string
    {
        $files = $this->getAvailableProductImages();
        return empty($files) ? null : 'products/' . $this->faker->randomElement($files);
    }

    private function randomVariantImage(): ?string
    {
        $files = Storage::disk('public')->files('variants');
        if (empty($files)) {
            return null;
        }
        return 'variants/' . basename($this->faker->randomElement($files));
    }
}
