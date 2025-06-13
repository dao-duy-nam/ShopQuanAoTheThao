<?php

namespace Database\Seeders;

use App\Models\MauSac;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MauSacSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $colors = ['Đỏ', 'Xanh', 'Đen', 'Trắng', 'Vàng'];
        foreach ($colors as $color) {
            MauSac::create(['ten_mau_sac' => $color]);
        }
    }
}
