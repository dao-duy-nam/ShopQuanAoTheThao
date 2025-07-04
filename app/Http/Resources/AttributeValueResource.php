<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttributeValueResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'              => $this->id,
            'gia_tri'         => $this->gia_tri,
            'thuoc_tinh_id'   => $this->thuoc_tinh_id,
            'thuoc_tinh_ten'  => $this->attribute->ten ?? null,
            'so_luong_bien_the' => $this->variants->count(),
            'created_at'      => $this->created_at?->format('Y-m-d H:i'),
            'updated_at'      => $this->updated_at?->format('Y-m-d H:i'),
        ];
    }
}
