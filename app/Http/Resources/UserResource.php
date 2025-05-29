<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */


    public function toArray($request)
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'email' => $this->email,
        'so_dien_thoai' => $this->so_dien_thoai,
        'vai_tro_id' => $this->vai_tro_id,
        'trang_thai' => $this->trang_thai,
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at,
    ];
}
}
