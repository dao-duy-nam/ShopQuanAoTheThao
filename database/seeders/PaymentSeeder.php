<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PaymentSeeder extends Seeder
{
    public function run()
    {
        DB::table('phuong_thuc_thanh_toans')->insert([
            ['id' => 1, 'ten' => 'cod', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'ten' => 'vnpay', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'ten' => 'momo', 'created_at' => now(), 'updated_at' => now()],
            
        ]);
    }
}
