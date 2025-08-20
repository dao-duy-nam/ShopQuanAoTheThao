<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'ten' => 'Áo thun thể thao nam',
                'mo_ta' => 'Áo thun thể thao nam co giãn 4 chiều, thấm hút nhanh, thoáng mát; phù hợp chạy bộ và tập gym hằng ngày.',
                'image' => 'category/aotapgymnam.jpg',
            ],
            [
                'ten' => 'Áo thun thể thao nữ',
                'mo_ta' => 'Áo thun thể thao nữ mềm mịn, ôm vừa vặn, thấm hút mồ hôi; thoải mái cho yoga, pilates và cardio.',
                'image' => 'category/aothunthethaonu.jfif',
            ],
            [
                'ten' => 'Áo khoác thể thao',
                'mo_ta' => 'Áo khoác thể thao chống gió, nhanh khô, nhẹ; thuận tiện khi tập luyện ngoài trời và di chuyển.',
                'image' => 'category/aokhoacthethao.jpg',
            ],
            [
                'ten' => 'Bộ đồ thể thao nam',
                'mo_ta' => 'Bộ đồ thể thao nam đồng bộ, chất liệu thoáng khí; phù hợp luyện tập và mặc thường ngày.',
                'image' => 'category/aotapgymnam.jpg',
            ],
            [
                'ten' => 'Bộ đồ thể thao nữ',
                'mo_ta' => 'Set đồ thể thao nữ co giãn tốt, tôn dáng, thấm hút mồ hôi; phù hợp phòng gym và yoga.',
                'image' => 'category/quanthethaonu.jpg',
            ],
            [
                'ten' => 'Quần short thể thao nam',
                'mo_ta' => 'Quần short thể thao nam nhẹ, nhanh khô, có túi; thích hợp đá bóng, chạy bộ và các bài tập cường độ cao.',
                'image' => 'category/quanshortthethaonam.jfif',
            ],
            [
                'ten' => 'Quần jogger nam',
                'mo_ta' => 'Quần jogger nam chất liệu co giãn, mềm mại; phù hợp tập luyện và mặc thường ngày.',
                'image' => 'category/quanjoggernam.jfif',
            ],
            [
                'ten' => 'Quần legging thể thao nữ',
                'mo_ta' => 'Legging nữ đàn hồi, ôm gọn cơ thể, chống trượt; thoải mái khi tập gym, yoga và chạy bộ.',
                'image' => 'category/quanthethaonu.jpg',
            ],
            [
                'ten' => 'Áo bóng đá',
                'mo_ta' => 'Áo bóng đá thoáng khí, nhẹ, thấm hút mồ hôi; phù hợp thi đấu và tập luyện.',
                'image' => 'category/aobongda.jpg',
            ],
            [
                'ten' => 'Áo bóng rổ',
                'mo_ta' => 'Áo bóng rổ rộng rãi, thoáng mát, nhanh khô; hỗ trợ vận động linh hoạt trên sân.',
                'image' => 'category/aobongro.jpg',
            ],
            [
                'ten' => 'Áo tập gym nam',
                'mo_ta' => 'Áo tập gym nam ôm gọn cơ thể, co giãn tốt; hạn chế bám mồ hôi khi vận động cường độ cao.',
                'image' => 'category/aotapgymnam.jpg',
            ],
            [
                'ten' => 'Áo bra thể thao nữ',
                'mo_ta' => 'Bra thể thao nữ nâng đỡ tốt, thấm hút nhanh, khô thoáng; phù hợp chạy bộ và tập luyện.',
                'image' => 'category/quanthethaonu.jpg',
            ],
        ];

        foreach ($categories as $data) {
            Category::create($data);
        }
    }
}
