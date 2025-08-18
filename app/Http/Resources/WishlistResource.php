<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WishlistResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'wishlist_id' => $this->id, 
            'product'     => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
