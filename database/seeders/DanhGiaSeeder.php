<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DanhGia;
use App\Models\User;
use App\Models\Product;
use App\Models\Variant;

class DanhGiaSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::inRandomOrder()->take(5)->get();
        $products = Product::inRandomOrder()->take(5)->get();
        $bienThes = Variant::inRandomOrder()->take(5)->get();

        // Kiểm tra dữ liệu đủ để seed chưa
        if ($users->isEmpty() || ($products->isEmpty() && $bienThes->isEmpty())) {
            $this->command->warn('⚠️ Không có đủ dữ liệu để seed DanhGia. Hãy seed User, Product và BienThe trước.');
            return;
        }

        $reviewSamples = [
            ['stars' => 5, 'content' => 'Chất liệu thoáng mát, thấm mồ hôi tốt. Mặc chạy bộ rất dễ chịu.'],
            ['stars' => 4, 'content' => 'Form vừa vặn, đường may chắc chắn. Màu sắc giống ảnh.'],
            ['stars' => 5, 'content' => 'Áo bra nâng đỡ tốt khi tập HIIT, rất hài lòng.'],
            ['stars' => 3, 'content' => 'Chất ok nhưng giao hàng hơi chậm 1 ngày.'],
            ['stars' => 4, 'content' => 'Quần legging co giãn ổn, không bị trượt khi squat.'],
            ['stars' => 5, 'content' => 'Áo khoác nhẹ, cản gió tốt, chạy buổi sáng rất thích.'],
            ['stars' => 4, 'content' => 'Giá hợp lý, chất lượng tương xứng. Sẽ ủng hộ tiếp.'],
            ['stars' => 5, 'content' => 'Size chuẩn, tư vấn nhiệt tình. Mua tặng bạn cũng ưng.'],
            ['stars' => 4, 'content' => 'Giày bám đường tốt, chạy máy êm chân.'],
            ['stars' => 5, 'content' => 'Áo thun ProDry mặc mát và nhanh khô, quá ổn!'],
        ];

        foreach ($reviewSamples as $sample) {
            $user = $users->random();

            $useVariant = $bienThes->isNotEmpty() && rand(0, 1);
            $useProduct = !$useVariant && $products->isNotEmpty();

            DanhGia::create([
                'user_id' => $user->id,
                'san_pham_id' => $useProduct ? $products->random()->id : null,
                'bien_the_id' => $useVariant ? $bienThes->random()->id : null,
                'noi_dung' => $sample['content'],
                'so_sao' => $sample['stars'],
                'hinh_anh' => null,
                'is_hidden' => false,
            ]);
        }

        $this->command->info('✅ DanhGiaSeeder đã chạy thành công.');
    }
}
