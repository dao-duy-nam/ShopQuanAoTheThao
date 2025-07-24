<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'ten'         => $this->ten,
            'mo_ta'       => $this->mo_ta,
            'so_luong'    => $this->so_luong,
            'hinh_anh' => $this->hinh_anh,
            'danh_muc_id' => $this->danh_muc_id,
            'ten_danh_muc'  => optional($this->category)->ten,
            'created_at'  => optional($this->created_at)->format('d/m/Y H:i'),
            'updated_at'  => optional($this->updated_at)->format('d/m/Y H:i'),
            'variants'    => VariantResource::collection($this->whenLoaded('variants')),
        ];
    }
}
