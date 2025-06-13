<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'              => $this->id,
            'ten'             => $this->ten,
            'gia'             => number_format($this->gia, 0, '.', ','),
            'gia_khuyen_mai'  => number_format($this->gia_khuyen_mai, 0, '.', ','),
            'so_luong'        => $this->so_luong,
            'mo_ta'           => $this->mo_ta,
            'hinh_anh'        => $this->hinh_anh,
            'danh_muc_id'     => $this->danh_muc_id,
            'created_at'      => $this->created_at ? $this->created_at->format('d/m/Y H:i') : null,
            'updated_at'      => $this->updated_at ? $this->updated_at->format('d/m/Y H:i') : null,
            'variants' => VariantResource::collection($this->variants),
        ];
    }
}
