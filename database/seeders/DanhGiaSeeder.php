<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DanhGia;
use App\Models\User;
use App\Models\BienThe;
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

        foreach (range(1, 10) as $i) {
            $user = $users->random();

            // Quyết định sẽ chọn product hay variant hay cả hai null
            $useVariant = $bienThes->isNotEmpty() && rand(0, 1);
            $useProduct = !$useVariant && $products->isNotEmpty();

            DanhGia::create([
                'user_id' => $user->id,
                'san_pham_id' => $useProduct ? $products->random()->id : null,
                'bien_the_id' => $useVariant ? $bienThes->random()->id : null,
                'noi_dung' => fake()->sentence(10),
                'so_sao' => rand(1, 5),
                'hinh_anh' => null,
                'is_hidden' => false,
            ]);
        }

        $this->command->info('✅ DanhGiaSeeder đã chạy thành công.');
    }
}
