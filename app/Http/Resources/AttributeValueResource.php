<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttributeValueResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'              => $this->id,
            'thuoc_tinh_id'   => $this->thuoc_tinh_id,
            'thuoc_tinh_ten'  => $this->attribute->ten ?? null,
            'gia_tri'         => $this->gia_tri,
            'created_at'      => $this->created_at?->format('Y-m-d H:i'),
            'updated_at'      => $this->updated_at?->format('Y-m-d H:i'),
        ];
    }
}
