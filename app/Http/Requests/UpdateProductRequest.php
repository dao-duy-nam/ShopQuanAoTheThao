<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
       $productId = $this->route('product');
        return [
             'ten'         => 'required|string|max:255|unique:san_phams,ten,' . $productId . ',id',
            'mo_ta'       => 'nullable|string',
            'hinh_anh'    => 'nullable|image|mimes:jpg,jpeg,png,webp',
            'danh_muc_id' => 'required|exists:danh_mucs,id',
        ];
    }

    public function messages(): array
    {
        return [
            'ten.required'       => 'Vui lòng nhập tên sản phẩm.',
            'ten.unique'         => 'Tên sản phẩm đã tồn tại.',
            'ten.string'         => 'Tên sản phẩm phải là chuỗi.',
            'ten.max'            => 'Tên sản phẩm không vượt quá 255 ký tự.',
            'mo_ta.string'       => 'Mô tả phải là chuỗi.',
            'hinh_anh.image'     => 'Hình ảnh phải là tệp hình ảnh.',
            'hinh_anh.mimes'     => 'Ảnh chỉ chấp nhận định dạng jpg, jpeg, png, webp.',
            'danh_muc_id.required' => 'Vui lòng chọn danh mục.',
            'danh_muc_id.exists'   => 'Danh mục không tồn tại.',
        ];
    }
}
