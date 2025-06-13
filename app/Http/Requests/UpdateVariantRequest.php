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
            'kich_co' => 'sometimes|required|string|max:255',
            'mau_sac' => 'sometimes|required|string|max:255',
            'so_luong' => 'sometimes|required|integer|min:0',
            'gia' => 'sometimes|required|numeric|min:0',
            'gia_khuyen_mai' => 'nullable|numeric|min:0',
            'images' => 'nullable|array|max:4',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }


    public function messages(): array
    {
        return [
            'kich_co.required' => 'Kích cỡ là bắt buộc.',
            'kich_co.string' => 'Kích cỡ phải là chuỗi ký tự.',
            'kich_co.max' => 'Kích cỡ không được vượt quá 255 ký tự.',
            'mau_sac.required' => 'Màu sắc là bắt buộc.',
            'mau_sac.string' => 'Màu sắc phải là chuỗi ký tự.',
            'mau_sac.max' => 'Màu sắc không được vượt quá 255 ký tự.',
            'so_luong.required' => 'Số lượng là bắt buộc.',
            'so_luong.integer' => 'Số lượng phải là số nguyên.',
            'so_luong.min' => 'Số lượng phải lớn hơn hoặc bằng 0.',
            'gia.required' => 'Giá là bắt buộc.',
            'gia.numeric' => 'Giá phải là số.',
            'gia.min' => 'Giá phải lớn hơn hoặc bằng 0.',
            'gia_khuyen_mai.numeric' => 'Giá khuyến mãi phải là số.',
            'gia_khuyen_mai.min' => 'Giá khuyến mãi phải lớn hơn hoặc bằng 0.',
            'images.array' => 'Hình ảnh phải là một danh sách.',
            'images.max' => 'Không được tải lên quá 4 hình ảnh.',
            'images.*.image' => 'Tệp tải lên phải là hình ảnh.',
            'images.*.mimes' => 'Hình ảnh phải có định dạng jpg, jpeg hoặc png.',
            'images.*.max' => 'Hình ảnh không được vượt quá 2MB.',
        ];
    }
}
