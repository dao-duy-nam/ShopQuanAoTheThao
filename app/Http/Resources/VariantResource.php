<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VariantResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'Size' => $this->Size?->kich_co,
            'Color' => $this->Color?->ten_mau_sac,
            'so_luong' => $this->so_luong,
            'gia' => $this->gia,
            'gia_khuyen_mai' => $this->gia_khuyen_mai,
        ];
    }
}
