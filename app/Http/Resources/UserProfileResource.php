<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         return [
            'id'            => $this->id,
            'name'          => $this->name,
            'email'         => $this->email,
            'phone'         => $this->so_dien_thoai,
            'anh_dai_dien'        => $this->anh_dai_dien ? asset('storage/' . $this->anh_dai_dien) : null,
            'gioi_tinh'     => $this->gioi_tinh,
            'ngay_sinh'     => $this->ngay_sinh,
            'address'       => new DiaChiResource($this->diaChis->first()), // địa chỉ mặc định
        ];
    }
}
