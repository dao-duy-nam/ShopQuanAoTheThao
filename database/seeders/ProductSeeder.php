<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Danh sách tên danh mục hợp lệ giống trong ProductFactory
        $validCategories = [
            'Áo thun thể thao nam',
            'Áo thun thể thao nữ',
            'Áo khoác thể thao',
            'Bộ đồ thể thao nam',
            'Bộ đồ thể thao nữ',
            'Quần short thể thao nam',
            'Quần jogger nam',
            'Quần legging thể thao nữ',
            'Áo bóng đá',
            'Áo bóng rổ',
            'Áo tập gym nam',
            'Áo bra thể thao nữ',
        ];
        foreach ($validCategories as $catName) {
            $category = Category::where('ten', $catName)->first();
            if (!$category) continue;
            // Lấy các mẫu tên chi tiết từ ProductFactory
            $detailNameTemplates = [
                'Áo thun thể thao nam' => [
                    'Áo thun nam cổ tròn', 'Áo thun nam cổ tim', 'Áo thun nam tay ngắn', 'Áo thun nam ôm body', 'Áo thun nam thể thao phối lưới', 'Áo thun nam thể thao thoáng khí', 'Áo thun nam chạy bộ',
                ],
                'Áo thun thể thao nữ' => [
                    'Áo thun nữ cổ tròn', 'Áo thun nữ tay lỡ', 'Áo thun nữ dáng rộng', 'Áo thun nữ thể thao ôm body', 'Áo thun nữ tập gym', 'Áo thun nữ thể thao thoáng khí', 'Áo thun nữ yoga',
                ],
                'Áo khoác thể thao' => [
                    'Áo khoác gió', 'Áo khoác chống nắng', 'Áo khoác 2 lớp', 'Áo khoác thể thao có mũ', 'Áo khoác thể thao nhẹ', 'Áo khoác thể thao chống nước', 'Áo khoác thể thao mùa đông',
                ],
                'Bộ đồ thể thao nam' => [
                    'Bộ thể thao nam 2 món', 'Bộ thể thao nam 3 món', 'Bộ đồ nam thể thao mùa hè', 'Bộ đồ nam thể thao mùa đông', 'Bộ thể thao nam phối màu', 'Bộ thể thao nam tập gym', 'Bộ thể thao nam chạy bộ',
                ],
                'Bộ đồ thể thao nữ' => [
                    'Bộ thể thao nữ 2 món', 'Bộ thể thao nữ 3 món', 'Bộ đồ nữ thể thao mùa hè', 'Bộ đồ nữ thể thao mùa đông', 'Bộ thể thao nữ phối màu', 'Bộ thể thao nữ tập gym', 'Bộ thể thao nữ yoga',
                ],
                'Quần short thể thao nam' => [
                    'Quần short nam 2 lớp', 'Quần short nam chạy bộ', 'Quần short nam tập gym', 'Quần short nam thể thao lưng thun', 'Quần short nam thoáng khí', 'Quần short nam đá bóng', 'Quần short nam tennis',
                ],
                'Quần jogger nam' => [
                    'Quần jogger nam bo gấu', 'Quần jogger nam thể thao', 'Quần jogger nam lưng thun', 'Quần jogger nam tập gym', 'Quần jogger nam chạy bộ', 'Quần jogger nam phối khoá', 'Quần jogger nam mùa đông',
                ],
                'Quần legging thể thao nữ' => [
                    'Quần legging nữ tập gym', 'Quần legging nữ yoga', 'Quần legging nữ co giãn', 'Quần legging nữ thể thao lưng cao', 'Quần legging nữ chạy bộ', 'Quần legging nữ ôm body', 'Quần legging nữ mùa đông',
                ],
                'Áo bóng đá' => [
                    'Áo bóng đá nam CLB', 'Áo bóng đá nam đội tuyển', 'Áo bóng đá nam cổ tròn', 'Áo bóng đá nam sân nhà', 'Áo bóng đá nam sân khách', 'Áo bóng đá nam tập luyện', 'Áo bóng đá nam không logo',
                ],
                'Áo bóng rổ' => [
                    'Áo bóng rổ nam NBA', 'Áo bóng rổ nam không tay', 'Áo bóng rổ nam cổ tròn', 'Áo bóng rổ nam đội tuyển', 'Áo bóng rổ nam tập luyện', 'Áo bóng rổ nam phối màu', 'Áo bóng rổ nam mùa hè',
                ],
                'Áo tập gym nam' => [
                    'Áo tanktop nam tập gym', 'Áo ba lỗ nam gym', 'Áo tập gym nam ôm body', 'Áo tập gym nam thoáng khí', 'Áo tập gym nam cổ tròn', 'Áo tập gym nam phối lưới', 'Áo tập gym nam chạy bộ',
                ],
                'Áo bra thể thao nữ' => [
                    'Áo bra nữ tập gym', 'Áo bra nữ chạy bộ', 'Áo bra nữ nâng đỡ tốt', 'Áo bra nữ thể thao lưng chéo', 'Áo bra nữ yoga', 'Áo bra nữ không gọng', 'Áo bra nữ phối lưới',
                ],
            ];
            $names = $detailNameTemplates[$catName];
            shuffle($names);
            for ($i = 0; $i < 6; $i++) {
                \App\Models\Product::factory()->create([
                    'ten' => $names[$i],
                    'danh_muc_id' => $category->id,
                ]);
            }
        }
    }
}



