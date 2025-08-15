<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDiscountCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ma' => 'required|string|unique:ma_giam_gias,ma,' . $this->route('id'),
            'ten' => 'nullable|string',
            'loai' => 'required|in:phan_tram,tien',
            'mo_ta' => 'nullable|string',
            'ap_dung_cho' => 'required|in:toan_don,san_pham',
            'san_pham_id' => 'nullable|exists:san_phams,id',
            'gia_tri' => 'required|integer|min:1',
            'gia_tri_don_hang' => 'nullable|integer|min:0',
            // 'so_luong' => 'required|integer|min:0',
            'gioi_han' => 'nullable|integer|min:0',
            'ngay_bat_dau' => 'nullable|date',
            'ngay_ket_thuc' => 'nullable|date|after:ngay_bat_dau',
            'trang_thai' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return (new StoreDiscountCodeRequest)->messages();
    }
}
