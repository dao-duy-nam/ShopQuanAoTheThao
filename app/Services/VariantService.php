<?php

namespace App\Services;

use App\Models\Color;
use App\Models\Size;
use App\Models\Variant;
use App\Models\Product;

class VariantService
{
    public function createVariants(Product $product, array $variants): void
    {
        foreach ($variants as $variant) {
            $kichCo = Size::firstOrCreate(
                ['kich_co' => $variant['kich_co']]
            );

            $mauSac = Color::firstOrCreate(
                ['ten_mau_sac' => $variant['mau_sac']]
            );

            Variant::create([
                'san_pham_id' => $product->id,
                'kich_co_id' => $kichCo->id,
                'mau_sac_id' => $mauSac->id,
                'so_luong' => $variant['so_luong'],
                'gia' => $variant['gia'],
                'gia_khuyen_mai' => $variant['gia_khuyen_mai'] ?? null,
            ]);
        }
    }
    public function updateVariants(Product $product, array $data): void
    {
        if (!empty($data['deleted_variant_ids'])) {
            $deletedIds = $data['deleted_variant_ids'];
            $validIds = Variant::where('san_pham_id', $product->id)
                ->whereIn('id', $deletedIds)
                ->pluck('id')
                ->toArray();
            $invalidIds = array_diff($deletedIds, $validIds);
            if (!empty($invalidIds)) {
                throw new \Exception('Các ID biến thể sau không tồn tại');
            }
            Variant::whereIn('id', $validIds)->delete();
        }
        foreach ($data['variants'] as $variant) {
            $kichCo = Size::firstOrCreate(
                ['kich_co' => $variant['kich_co']],
                ['created_at' => now(), 'updated_at' => now()]
            );
            $mauSac = Color::firstOrCreate(
                ['ten_mau_sac' => $variant['mau_sac']],
                ['created_at' => now(), 'updated_at' => now()]
            );
            if (!empty($variant['id'])) {
                $variantModel = Variant::where('id', $variant['id'])
                    ->where('san_pham_id', $product->id)
                    ->first();
                if ($variantModel) {
                    $variantModel->update([
                        'kich_co_id' => $kichCo->id,
                        'mau_sac_id' => $mauSac->id,
                        'so_luong' => $variant['so_luong'],
                        'gia' => $variant['gia'],
                        'gia_khuyen_mai' => $variant['gia_khuyen_mai'] ?? null,
                    ]);
                }
            } else {
                Variant::create([
                    'san_pham_id' => $product->id,
                    'kich_co_id' => $kichCo->id,
                    'mau_sac_id' => $mauSac->id,
                    'so_luong' => $variant['so_luong'],
                    'gia' => $variant['gia'],
                    'gia_khuyen_mai' => $variant['gia_khuyen_mai'] ?? null,
                ]);
            }
        }
    }
}
