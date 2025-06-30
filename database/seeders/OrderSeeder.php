<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Variant;
use App\Models\OrderDetail;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $userIds    = User::pluck('id');
        $productIds = Product::pluck('id');
        $paymentMethodIds  = PaymentMethod::pluck('id');
        if ($userIds->isEmpty() || $productIds->isEmpty()) {
            $this->command->warn('Cần có user và sản phẩm trước khi seed đơn hàng.');
            return;
        }
        for ($i = 1; $i <= 10; $i++) {
            $order = Order::create([
                'ma_don_hang'              => 'DH' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'user_id'                  => $userIds->random(),
                'phuong_thuc_thanh_toan_id'=> $paymentMethodIds->random(),
                'trang_thai_don_hang'      => fake()->randomElement(['cho_xac_nhan', 'dang_chuan_bi', 'dang_van_chuyen', 'da_giao']),
                'trang_thai_thanh_toan'    => fake()->randomElement(['cho_xu_ly', 'da_thanh_toan']),
                'dia_chi'                  => fake()->address(),
                'so_tien_thanh_toan'       => 0,
            ]);
            $tongTien = 0;
            $selectedProducts = $productIds->random(rand(1, 3));
            foreach ($selectedProducts as $productId) {
                $quantity   = rand(1, 5);
                $unitPrice  = rand(50000, 300000);
                $totalPrice = $quantity * $unitPrice;
                $variant = Variant::where('san_pham_id', $productId)
                    ->with('attributeValues.attribute')
                    ->inRandomOrder()
                    ->first();
                $thuocTinhBienThe = null;
                if ($variant && $variant->attributeValues->isNotEmpty()) {
                    $thuocTinhBienThe = $variant->attributeValues->map(function ($value) {
                        return [
                            'thuoc_tinh' => $value->thuocTinh->ten ?? 'Không rõ',
                            'gia_tri'    => $value->gia_tri ?? 'N/A',
                        ];
                    })->toArray();
                }
                OrderDetail::create([
                    'don_hang_id'          => $order->id,
                    'san_pham_id'          => $productId,
                    'bien_the_id'          => $variant?->id,
                    'so_luong'             => $quantity,
                    'don_gia'              => $unitPrice,
                    'tong_tien'            => $totalPrice,
                    'thuoc_tinh_bien_the' => json_encode($thuocTinhBienThe),
                ]);
                $tongTien += $totalPrice;
            }
            $order->update(['so_tien_thanh_toan' => $tongTien]);
        }
    }
}
