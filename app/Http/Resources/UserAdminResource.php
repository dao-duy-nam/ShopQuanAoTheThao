<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAdminResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         return [
            'id'           => $this->id,
            'name'         => $this->name,
            'email'        => $this->email,
            'phone'        => $this->so_dien_thoai,
            'status'       => $this->trang_thai,
            'avatar'       => $this->anh_dai_dien ? asset('storage/' . $this->anh_dai_dien) : null,
            'role'         => [
                'id'    => $this->vai_tro_id,
                'name'  => optional($this->role)->ten_vai_tro,
            ],
            'blockedInfo' => [
                'reason'     => $this->ly_do_block,
                'until'      => $this->block_den_ngay,
                'type'       => $this->kieu_block,
            ],
            'createdAt'    => $this->created_at,
            'updatedAt'    => $this->updated_at,
            'addresses'    => DiaChiResource::collection($this->whenLoaded('diaChis')),
        ];
    }
}
