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
            'attributes' => 'sometimes|array|min:1',
            'attributes.*.thuoc_tinh_id' => 'required_with:attributes|integer|exists:thuoc_tinhs,id',
            'attributes.*.gia_tri' => 'required_with:attributes|string|max:255',

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
            'attributes.array' => 'Thuộc tính phải là một mảng.',
            'attributes.min' => 'Phải có ít nhất một thuộc tính.',
            'attributes.*.thuoc_tinh_id.required_with' => 'ID thuộc tính là bắt buộc khi có thuộc tính.',
            'attributes.*.thuoc_tinh_id.integer' => 'ID thuộc tính phải là một số nguyên.',
            'attributes.*.thuoc_tinh_id.exists' => 'ID thuộc tính không tồn tại trong cơ sở dữ liệu.',
            'attributes     .*.gia_tri.required_with' => 'Giá trị thuộc tính là bắt buộc khi có thuộc tính.',
            'attributes.*.gia_tri.string' => 'Giá trị thuộc tính phải là một chuỗi.',
            'attributes.*.gia_tri.max' => 'Giá trị thuộc tính không được vượt quá 255 ký tự.',
        ];
    }
}
