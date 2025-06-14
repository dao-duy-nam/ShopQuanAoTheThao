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
                'hinh_anh' => $this->encodeImages($variant['hinh_anh'] ?? null),
            ]);
        }
    }
    protected function encodeImages($images): ?string
    {
        $paths = [];
        if ($images instanceof \Illuminate\Http\UploadedFile) {
            $images = [$images];
        }
        if (is_array($images)) {
            foreach ($images as $image) {
                if ($image instanceof \Illuminate\Http\UploadedFile) {
                    $paths[] = $image->store('variants', 'public');
                }
            }
        }
        return !empty($paths) ? json_encode($paths) : null;
    }
}
