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
            ['id' => 1, 'ten' => 'Thanh toán khi nhận hàng (COD)', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'ten' => 'Thanh toán qua VNPAY', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'ten' => 'Thanh toán qua ZaloPay', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
