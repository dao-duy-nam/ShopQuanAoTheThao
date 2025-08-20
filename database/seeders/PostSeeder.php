<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('vi_VN');

        $localImages = [
            '5bff93b7-027c-445a-9057-aca0e651044f.jpg',
            '39d8343e-8503-4c58-8826-2d436036b0c7.jpg',
            '677bd66c-9aaa-4709-a87a-9c6d5a5ae640.jpg',
            '7f197c88-4f46-430e-bdb1-231bd11b5860.jpg',
            '324c3af8-8450-49c7-8956-ada5e7755e39.jpg',
            '811baaf5-6d80-4313-aa05-7e81070facb6.jpg',
            'ac4a56f1-52d6-4e97-82fa-431989184c60.jpg',
            'e76af41b-3bf2-4903-98d8-0dc31946163a.jpg',
            '11a7f299-5284-4a01-b103-8107150cd7ed.jpg',
            '473ebc23-28ac-4c44-bc6e-963289106996.jpg',
            'a76e7422-0422-4b40-a952-9e393bf4a545.jpg',
            'e192ebf7-c10c-451e-9a38-173ef7775330.jpg',
            'Luu=fl.jpg',
        ];

        $externalImages = [
            'https://i.pinimg.com/736x/f5/e8/c5/f5e8c550976838663d719335378ea0f6.jpg',
            'https://i.pinimg.com/1200x/2a/15/35/2a1535c830520a4a840f7ac09a525d11.jpg',
            'https://i.pinimg.com/1200x/0c/3d/d9/0c3dd9d9f67f1bf5443aea26d35a9c10.jpg',
        ];

        $realisticTitles = [
            '5 cách chọn áo thun thể thao phù hợp cho mùa hè',
            'Bí quyết phối đồ thể thao nam năng động',
            'Xu hướng thời trang thể thao nữ năm 2024',
            'Lợi ích của việc mặc quần jogger khi tập gym',
            'Hướng dẫn bảo quản áo khoác thể thao đúng cách',
            'Top 7 mẫu áo bóng đá hot nhất hiện nay',
            'Tại sao nên chọn quần legging cho yoga?',
            'Cách phân biệt áo tập gym chính hãng',
            'Mẹo chọn size áo bra thể thao nữ chuẩn',
            'Những lưu ý khi mua bộ đồ thể thao online',
            'Tư vấn chọn giày thể thao phù hợp từng bộ môn',
            'Các loại vải phổ biến trong thời trang thể thao',
            'Cách phối màu trang phục thể thao cá tính',
            'Bí quyết giữ quần short thể thao luôn như mới',
            'Tập luyện hiệu quả hơn với trang phục phù hợp',
            'Review các mẫu áo bóng rổ bán chạy',
            'Chọn áo khoác thể thao cho mùa đông',
            'Những mẫu áo thun thể thao nữ được yêu thích',
            'Cách chọn bộ đồ thể thao cho người mới bắt đầu',
            'Tổng hợp các mẫu quần short nam hot trend',
        ];
        $realisticParagraphs = [
            'Trang phục thể thao ngày càng đa dạng về mẫu mã, chất liệu và kiểu dáng, đáp ứng nhu cầu tập luyện cũng như thời trang.',
            'Việc lựa chọn đúng loại áo, quần sẽ giúp bạn cảm thấy thoải mái, tự tin và nâng cao hiệu quả tập luyện.',
            'Áo thun thể thao thường được làm từ chất liệu thấm hút mồ hôi tốt, co giãn 4 chiều, phù hợp cho các hoạt động vận động mạnh.',
            'Quần jogger, quần short hay legging đều có những ưu điểm riêng, phù hợp với từng bộ môn thể thao khác nhau.',
            'Khi chọn mua áo bra thể thao, bạn nên chú ý đến độ nâng đỡ, chất liệu và size để đảm bảo sự thoải mái.',
            'Bảo quản trang phục thể thao đúng cách sẽ giúp kéo dài tuổi thọ và giữ được form dáng ban đầu.',
            'Xu hướng thời trang thể thao năm nay tập trung vào sự tối giản, màu sắc trung tính và thiết kế đa năng.',
            'Bạn nên ưu tiên các sản phẩm chính hãng để đảm bảo chất lượng và an toàn cho sức khỏe.',
            'Phối đồ thể thao không chỉ giúp bạn nổi bật mà còn thể hiện cá tính riêng.',
            'Hãy tham khảo ý kiến chuyên gia hoặc nhân viên bán hàng khi chọn trang phục thể thao cho từng mục đích sử dụng.',
        ];
        for ($i = 0; $i < 20; $i++) {
            $title = $realisticTitles[$i % count($realisticTitles)];
            $shortDesc = $realisticParagraphs[array_rand($realisticParagraphs)];
            shuffle($realisticParagraphs);
            $paragraphs = array_slice($realisticParagraphs, 0, rand(6, 10));
            $content = '';
            $imageSrc = $faker->randomElement($externalImages);
            $content .= "<p><img src=\"{$imageSrc}\" alt=\"Ảnh minh họa\" style=\"width:300px;height:200px;object-fit:cover;display:block;margin:15px auto;\"></p>";
            foreach ($paragraphs as $para) {
                $content .= "<p>{$para}</p>";
            }
            DB::table('bai_viet')->insert([
                'tieu_de' => $title,
                'mo_ta_ngan' => $shortDesc,
                'noi_dung' => $content,
                'anh_dai_dien' => 'posts/' . $faker->randomElement($localImages),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
