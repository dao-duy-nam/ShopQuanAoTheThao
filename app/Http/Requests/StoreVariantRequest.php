<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVariantRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
           'so_luong' => 'required|integer|min:0',
            'gia' => 'required|numeric|min:0',
            'gia_khuyen_mai' => 'nullable|numeric|min:0',
            'images.*' => 'nullable|image',
            'attributes' => 'required|array',
            'attributes.*.thuoc_tinh_id' => 'required|integer|exists:thuoc_tinhs,id',
            'attributes.*.gia_tri' => 'required|string|max:255',
        ];
    }


    public function messages(): array
    {
        return [
            'so_luong.required' => 'Số lượng là bắt buộc.',
            'so_luong.integer' => 'Số lượng phải là số nguyên.',
            'so_luong.min' => 'Số lượng phải lớn hơn hoặc bằng 0.',
            'gia.required' => 'Giá là bắt buộc.',
            'gia.numeric' => 'Giá phải là số.',
            'gia.min' => 'Giá phải lớn hơn hoặc bằng 0.',
            'gia_khuyen_mai.numeric' => 'Giá khuyến mãi phải là số.',
            'gia_khuyen_mai.min' => 'Giá khuyến mãi phải lớn hơn hoặc bằng 0.',
            'images.*.image' => 'Tệp tải lên phải là hình ảnh.',
            'attributes.required' => 'Danh sách thuộc tính là bắt buộc.',
            'attributes.*.thuoc_tinh_id.required' => 'ID thuộc tính là bắt buộc.',
            'attributes.*.thuoc_tinh_id.integer' => 'ID thuộc tính phải là số nguyên.',
            'attributes.*.thuoc_tinh_id.exists' => 'ID thuộc tính không tồn tại.',
            'attributes.*.gia_tri.required' => 'Giá trị thuộc tính là bắt buộc.',
            'attributes.*.gia_tri.string' => 'Giá trị thuộc tính phải là chuỗi.',
            'attributes.*.gia_tri.max' => 'Giá trị thuộc tính không được vượt quá 255 ký tự.',
        ];
    }
}
