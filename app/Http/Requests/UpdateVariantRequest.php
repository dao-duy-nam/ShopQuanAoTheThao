<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'so_luong' => 'nullable|integer|min:0',
            'gia' => 'nullable|numeric|min:0',
            'gia_khuyen_mai' => 'nullable|numeric|min:0',
            'images.*' => 'nullable|image',
        ];
    }


    public function messages(): array
    {
        return [
            'so_luong.integer' => 'Số lượng phải là số nguyên.',
            'so_luong.min' => 'Số lượng phải lớn hơn hoặc bằng 0.',
            'gia.numeric' => 'Giá phải là số.',
            'gia.min' => 'Giá phải lớn hơn hoặc bằng 0.',
            'gia_khuyen_mai.numeric' => 'Giá khuyến mãi phải là số.',
            'gia_khuyen_mai.min' => 'Giá khuyến mãi phải lớn hơn hoặc bằng 0.',
            'images.*.image' => 'Tệp tải lên phải là hình ảnh.',
        ];
    }
}
