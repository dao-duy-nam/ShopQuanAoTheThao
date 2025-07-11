<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiaChiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'tinhThanh'     => $this->tinh_thanh,
            'quanHuyen'     => $this->quan_huyen,
            'phuongXa'      => $this->phuong_xa,
            'chiTiet'       => $this->dia_chi_chi_tiet,
            'macDinh'       => $this->mac_dinh,
        ];
    }
}
