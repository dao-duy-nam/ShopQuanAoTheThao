<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123456'),
            'so_dien_thoai' => '0900000000',
            'vai_tro_id' => 1,
            'trang_thai' => 'active',
            'ngay_sinh' => '1990-01-01',
            'email_verified_at' => Carbon::now(),
        ]);

        
        User::create([
            'name' => 'NGoc',
            'email' => 'ngocnxph50224@gmail.com',
            'password' => Hash::make('123456'),
            'so_dien_thoai' => '0869541205',
            'vai_tro_id' => 2,
            'trang_thai' => 'active',
            'ngay_sinh' => '1990-01-01',
            'email_verified_at' => Carbon::now(),
        ]);

        User::create([
            'name' => 'Duy Nam',
            'email' => 'namddph50247@gmail.com',
            'password' => bcrypt('123456'),
            'so_dien_thoai' => '0123456781',
            'vai_tro_id' => 2,
            'trang_thai' => 'active',
            'ngay_sinh' => '1999-01-11',
            'email_verified_at' => Carbon::now(),
        ]);

        User::create([
            'name' => 'Thanh Lake',
            'email' => 'thanhhbph50161@gmail.com',
            'password' => bcrypt('admin1'),
            'so_dien_thoai' => '0987654322',
            'vai_tro_id' => 1,
            'trang_thai' => 'active',
            'ngay_sinh' => '1998-05-25',
            'email_verified_at' => Carbon::now(),
        ]);

   
        for ($i = 1; $i <= 20; $i++) {
            User::create([
                'name' => 'User ' . $i,
                'email' => 'user' . $i . '@example.com',
                'password' => bcrypt('userpassword'),
                'so_dien_thoai' => '09123456' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'vai_tro_id' => 2,
                'trang_thai' => 'active',
                'ngay_sinh' => '2000-01-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'email_verified_at' => null,
            ]);
        }
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name' => 'Admin Fake ' . $i,
                'email' => 'admin_fake' . $i . '@example.com',
                'password' => bcrypt('adminpass'),
                'so_dien_thoai' => '09088888' . $i,
                'vai_tro_id' => 1,
                'trang_thai' => 'active',
                'ngay_sinh' => $faker->date('Y-m-d', '-30 years'),
                'email_verified_at' => Carbon::now(),
            ]);
        }

        
        for ($i = 1; $i <= 15; $i++) {
            User::create([
                'name' => 'Nhân viên ' . $i,
                'email' => 'nhanvien' . $i . '@example.com',
                'password' => bcrypt('nhanvienpass'),
                'so_dien_thoai' => '09333333' . $i,
                'vai_tro_id' => 3,
                'trang_thai' => 'active',
                'ngay_sinh' => $faker->date('Y-m-d', '-25 years'),
                'email_verified_at' => Carbon::now(),
            ]);
        }
    }
}
