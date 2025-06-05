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
            'name' => 'Nguyễn Văn abc',
            'email' => 'nguyenvanacc1a@example.com',
            'password' => bcrypt('password12346'),
            'so_dien_thoai' => '0123456781',
            'vai_tro_id' => 1,
            'trang_thai' => 'active',
            'ngay_sinh' => '1999-01-11', // thêm ngày sinh
        ]);

        // Dữ liệu giả cho người dùng 2
        User::create([
            'name' => 'Trần Thị cach',
            'email' => 'tranthibccaa10@example.com',
            'password' => bcrypt('password12346'),
            'so_dien_thoai' => '0987654322',
            'vai_tro_id' => 2,
            'trang_thai' => 'active',
            'ngay_sinh' => '1998-05-25', // thêm ngày sinh
        ]);
    }
}
