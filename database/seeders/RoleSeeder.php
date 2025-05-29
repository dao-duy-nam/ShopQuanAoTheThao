<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create(['ten_vai_tro' => 'Admin', 'mo_ta' => 'Quản trị viên']);
        Role::create(['ten_vai_tro' => 'User', 'mo_ta' => 'Người dùng thông thường']);
    }
}
