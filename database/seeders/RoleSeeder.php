<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::Create(
            ['ten_vai_tro' => 'admin'],
            ['mo_ta' => 'Quản trị viên']
        );

        Role::Create(
            ['ten_vai_tro' => 'user'],
            ['mo_ta' => 'Người dùng thông thường']
        );

        Role::Create(
            ['ten_vai_tro' => 'staff'],
            ['mo_ta' => 'Nhân viên ']
        );
    }
}
