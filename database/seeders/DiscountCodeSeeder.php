<?php

namespace Database\Seeders;

use App\Models\DiscountCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DiscountCodeSeeder extends Seeder
{
    public function run(): void
    {
        $ranks = [
            'ĐỒNG' => 10,
            'BẠC' => 15,
            'VÀNG' => 20,
            'BẠCH KIM' => 25,
            'KIM CƯƠNG' => 30,
        ];

        foreach ($ranks as $rank => $percent) {
            DiscountCode::create([
                'ma' => strtoupper('RANK_' . Str::slug($rank, '_')),
                'ten' => 'Mã giảm giá cho rank ' . $rank,
                'loai' => 'phan_tram',
                'ap_dung_cho' => 'toan_don',
                'san_pham_id' => null,
                'gia_tri' => $percent, 
                'gia_tri_don_hang' => 100000, 
                'mo_ta' => 'Mã giảm ' . $percent . '% cho khách hàng hạng ' . $rank,
                'so_lan_su_dung' => 0,
                'gioi_han' => 1, 
                'ngay_bat_dau' => now(),
                'ngay_ket_thuc' =>null,
                'trang_thai' => true,
            ]);
        }
    }
}
