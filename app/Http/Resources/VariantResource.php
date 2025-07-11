<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VariantResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'so_luong' => $this->so_luong,
            'gia' => $this->gia,
            'gia_khuyen_mai' => $this->gia_khuyen_mai,
            'hinh_anh' => $this->hinh_anh,
            'thuoc_tinh' => $this->attributeValues->map(function ($value) {
                return [
                    'ten' => optional($value->attribute)->ten,
                    'gia_tri' => $value->gia_tri,
                ];
            })->values(), // reset key -> 0,1,2
        ];
    }
}
