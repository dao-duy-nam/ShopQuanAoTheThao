<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Dữ liệu giả cho người dùng 1
        User::create([
            'name' => 'Nguyễn Văn A',
            'email' => 'nguyenvana@example.com',
            'password' => bcrypt('password123'),
            'so_dien_thoai' => '0123456789',
            'vai_tro_id' => 1,
            'trang_thai' => 'active',
            'ngay_sinh' => '1990-01-01', // thêm ngày sinh
        ]);

        // Dữ liệu giả cho người dùng 2
        User::create([
            'name' => 'Trần Thị B',
            'email' => 'tranthib@example.com',
            'password' => bcrypt('password123'),
            'so_dien_thoai' => '0987654321',
            'vai_tro_id' => 2,
            'trang_thai' => 'active',
            'ngay_sinh' => '1992-05-15', // thêm ngày sinh
        ]);
    }
}
