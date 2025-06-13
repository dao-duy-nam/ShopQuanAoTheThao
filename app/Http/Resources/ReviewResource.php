<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'san_pham_id'   => $this->san_pham_id,
            'bien_the_id'   => $this->bien_the_id,
            'noi_dung'      => $this->noi_dung,
            'so_sao'        => $this->so_sao,
            'hinh_anh'      => is_array($this->hinh_anh) ? $this->hinh_anh : json_decode($this->hinh_anh, true),
            'is_hidden'     => $this->is_hidden,
            'created_at'    => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at'    => $this->updated_at->format('Y-m-d H:i:s'),
            
            // Thông tin người dùng viết đánh giá
            'user' => [
                'id'     => $this->user->id ?? null,
                'name'   => $this->user->name ?? null,
                'email'  => $this->user->email ?? null,
                'avatar' => $this->user->avatar ?? null,
            ],
        ];
    }
}
