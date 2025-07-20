<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DiscountCodeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'ma' => $this->ma,
            'ten' => $this->ten,
            'mo_ta' => $this->mo_ta,
            'loai' => $this->loai,
            'ap_dung_cho' => $this->ap_dung_cho,
            'san_pham_id' => $this->san_pham_id,
            'gia_tri' => (int) $this->gia_tri,
            'gia_tri_don_hang' => $this->gia_tri_don_hang ? (int) $this->gia_tri_don_hang : null,
            'so_luong' => (int) $this->so_luong,
            'gioi_han' => $this->gioi_han ? (int) $this->gioi_han : null,
            'trang_thai' => (bool) $this->trang_thai,
            'ngay_bat_dau' => optional($this->ngay_bat_dau)->format('Y-m-d H:i:s'),
            'ngay_ket_thuc' => optional($this->ngay_ket_thuc)->format('Y-m-d H:i:s'),
            'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)->format('Y-m-d H:i:s'),
        ];
    }
}
