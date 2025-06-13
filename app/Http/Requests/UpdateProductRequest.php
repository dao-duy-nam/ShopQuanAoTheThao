<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
             'ten'             => 'required|string|max:255',
            'gia'             => 'required|numeric|min:0',
            'gia_khuyen_mai'  => 'nullable|numeric|min:0',
            'so_luong'        => 'required|integer|min:0',
            'mo_ta'           => 'nullable|string',
            'hinh_anh'        => 'nullable|string|url',
            'hinh_anh_public_id' => 'nullable|string',
            'danh_muc_id'     => 'required|exists:danh_mucs,id',
        ];
    }
    public function messages(): array
    {
        return [
           'ten.required' => 'Tên sản phẩm không được để trống.',
            'ten.string' => 'Tên sản phẩm phải là chuỗi.',
            'ten.max' => 'Tên sản phẩm không được vượt quá 255 ký tự.',
            'gia.required' => 'Giá sản phẩm không được để trống.',
            'gia.numeric' => 'Giá sản phẩm phải là số.',
            'gia.min' => 'Giá sản phẩm phải lớn hơn hoặc bằng 0.',
            'gia_khuyen_mai.numeric' => 'Giá khuyến mãi phải là số.',
            'gia_khuyen_mai.min' => 'Giá khuyến mãi phải lớn hơn hoặc bằng 0.',
            'so_luong.required' => 'Số lượng sản phẩm không được để trống.',
            'so_luong.integer' => 'Số lượng phải là số nguyên.',
            'so_luong.min' => 'Số lượng phải lớn hơn hoặc bằng 0.',
            'mo_ta.string' => 'Mô tả phải là chuỗi.',
            'hinh_anh.string' => 'Hình ảnh phải là chuỗi.',
            'hinh_anh.url' => 'Hình ảnh phải là đường dẫn hợp lệ.',
            'hinh_anh_public_id.string' => 'ID hình ảnh phải là chuỗi.',
            'danh_muc_id.required' => 'Danh mục sản phẩm không được để trống.',
            'danh_muc_id.exists' => 'Danh mục sản phẩm không hợp lệ.',
        ];
    }
}
