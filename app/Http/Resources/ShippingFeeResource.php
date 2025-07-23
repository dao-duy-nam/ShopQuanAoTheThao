<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShippingFeeResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'tinh_thanh' => $this->tinh_thanh,
            'phi' => $this->phi,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
