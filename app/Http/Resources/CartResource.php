<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'san_pham_id' => $this->san_pham_id,
            'ten_san_pham' => $this->sanPham->ten,
            'hinh_anh' => $this->sanPham->hinh_anh,
            'so_luong' => $this->so_luong,
            'gia_san_pham' => $this->gia_san_pham,
            'thanh_tien' => $this->thanh_tien,
            'bien_the' => $this->when($this->bien_the_id, function () {
                return [
                    'id' => $this->bienThe->id,
                    'thuoc_tinh' => $this->bienThe->attributeValues->map(function ($attrValue) {
                        return [
                            'ten_thuoc_tinh' => $attrValue->attribute->ten,
                            'gia_tri' => $attrValue->gia_tri
                        ];
                    })
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 