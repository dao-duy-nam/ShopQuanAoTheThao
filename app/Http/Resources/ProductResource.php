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
            'gia_khuyen_mai'  => number_format($this->gia_khuyen_mai ?? 0, 0, '.', ','), // xử lý null
            'so_luong'        => $this->so_luong,
            'mo_ta'           => $this->mo_ta,
            'hinh_anh'        => is_array($this->hinh_anh) ? $this->hinh_anh : json_decode($this->hinh_anh, true), // nếu là json
            'danh_muc_id'     => $this->danh_muc_id,
            'created_at'      => optional($this->created_at)->format('d/m/Y H:i'),
            'updated_at'      => optional($this->updated_at)->format('d/m/Y H:i'),
            'variants'        => VariantResource::collection($this->whenLoaded('variants')),
        ];
    }
}
