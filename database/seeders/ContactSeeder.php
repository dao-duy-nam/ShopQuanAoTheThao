<?php

namespace Database\Seeders;

use App\Models\Contact;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contacts = [
            [
                'name' => 'Nguyễn Minh Anh',
                'email' => 'minhanh@example.com',
                'phone' => '0912345678',
                'subject' => 'Tư vấn size áo thun thể thao',
                'message' => 'Mình cao 1m70 nặng 65kg thì chọn size nào cho áo thun ProDry?',
                'type' => 'ho_tro',
                'status' => 'chua_xu_ly',
                'replied_at' => null,
                'attachment' => null,
            ],
            [
                'name' => 'Trần Thu Trang',
                'email' => 'trangtran@example.com',
                'phone' => '0987654321',
                'subject' => 'Góp ý giao diện website',
                'message' => 'Phần lọc theo kích cỡ nên hiển thị nhiều lựa chọn hơn, rất cảm ơn shop.',
                'type' => 'gop_y',
                'status' => 'dang_xu_ly',
                'replied_at' => Carbon::now()->subDays(1),
                'attachment' => null,
            ],
            [
                'name' => 'Lê Quốc Huy',
                'email' => 'quochuy@example.com',
                'phone' => '0909123456',
                'subject' => 'Hợp tác cung cấp đồng phục CLB',
                'message' => 'CLB bóng đá của mình muốn đặt áo đồng phục 30 chiếc, xin báo giá.',
                'type' => 'hop_tac',
                'status' => 'dang_xu_ly',
                'replied_at' => Carbon::now()->subDays(2),
                'attachment' => null,
            ],
            [
                'name' => 'Phạm Hải Yến',
                'email' => 'haiyen@example.com',
                'phone' => '0978111222',
                'subject' => 'Hỏi tồn kho quần legging',
                'message' => 'Mẫu legging AirFlex màu đen size M còn hàng ở HN không ạ?',
                'type' => 'ho_tro',
                'status' => 'chua_xu_ly',
                'replied_at' => null,
                'attachment' => null,
            ],
            [
                'name' => 'Đinh Công Tuấn',
                'email' => 'congtuan@example.com',
                'phone' => '0966333444',
                'subject' => 'Chính sách đổi trả',
                'message' => 'Nếu áo không vừa size thì đổi trong bao lâu và phí như thế nào?',
                'type' => 'ho_tro',
                'status' => 'chua_xu_ly',
                'replied_at' => null,
                'attachment' => null,
            ],
        ];

        foreach ($contacts as $data) {
            Contact::create($data);
        }
    }
}

