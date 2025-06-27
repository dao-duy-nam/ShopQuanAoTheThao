<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'san_pham_id' => 'required|exists:san_phams,id',
            'so_luong' => 'required|integer|min:1',
            'bien_the_id' => 'nullable|exists:bien_thes,id',
        ];
    }

    public function messages(): array
    {
        return [
            'san_pham_id.required' => 'Vui lòng chọn sản phẩm',
            'san_pham_id.exists' => 'Sản phẩm không tồn tại',
            'so_luong.required' => 'Vui lòng nhập số lượng',
            'so_luong.integer' => 'Số lượng phải là số nguyên',
            'so_luong.min' => 'Số lượng phải lớn hơn 0',
            'bien_the_id.exists' => 'Biến thể sản phẩm không tồn tại',
        ];
    }
} 