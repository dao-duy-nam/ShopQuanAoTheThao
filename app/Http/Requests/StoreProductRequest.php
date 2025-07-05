<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ten'         => 'required|string|max:255|unique:san_phams,ten',
            'mo_ta'       => 'nullable|string',
            'hinh_anh'    => 'nullable|image|mimes:jpg,jpeg,png,webp',
            'danh_muc_id' => 'required|exists:danh_mucs,id',
            'variants'                          => 'required|array|min:1',
            'variants.*.so_luong'               => 'required|integer|min:0',
            'variants.*.gia'                    => 'required|numeric|min:0',
            'variants.*.gia_khuyen_mai'         => 'nullable|numeric|min:0',
            'variants.*.hinh_anh' => 'nullable|array',
            'variants.*.hinh_anh.*' => 'nullable|image|mimes:jpg,jpeg,png,webp',

            'variants.*.attributes'                             => 'nullable|array',
            'variants.*.attributes.*.thuoc_tinh_id'             => 'required|integer|exists:thuoc_tinhs,id',
            'variants.*.attributes.*.gia_tri'                   => 'nullable|string|max:255',
            'variants.*.attributes.*.gia_tri_thuoc_tinh_id'     => 'nullable|integer|exists:gia_tri_thuoc_tinhs,id',
        ];
    }

    public function messages(): array
    {
        return [
            'ten.required'   => 'Vui lòng nhập tên sản phẩm.',
            'ten.unique'     => 'Tên sản phẩm đã tồn tại, vui lòng chọn tên khác.',
            'ten.string'     => 'Tên sản phẩm phải là chuỗi ký tự.',
            'ten.max'        => 'Tên sản phẩm không vượt quá 255 ký tự.',
            'mo_ta.string'   => 'Mô tả sản phẩm phải là chuỗi ký tự.',
            'hinh_anh.image' => 'Tệp phải là hình ảnh.',
            'hinh_anh.mimes' => 'Ảnh chỉ chấp nhận jpg, jpeg, png, webp.',
            'danh_muc_id.required' => 'Vui lòng chọn danh mục.',
            'danh_muc_id.exists'   => 'Danh mục không tồn tại.',

            'variants.required' => 'Sản phẩm phải có ít nhất một biến thể.',
            'variants.array'    => 'Trường biến thể phải là mảng.',
            'variants.min'      => 'Phải có ít nhất một biến thể.',
            'variants.*.so_luong.required' => 'Vui lòng nhập số lượng cho biến thể.',
            'variants.*.so_luong.integer'  => 'Số lượng phải là số nguyên.',
            'variants.*.so_luong.min'      => 'Số lượng không được nhỏ hơn 0.',
            'variants.*.gia.required'      => 'Vui lòng nhập giá cho biến thể.',
            'variants.*.gia.numeric'       => 'Giá phải là số.',
            'variants.*.gia.min'           => 'Giá không được nhỏ hơn 0.',
            'variants.*.gia_khuyen_mai.numeric' => 'Giá khuyến mãi phải là số.',
            'variants.*.gia_khuyen_mai.min'     => 'Giá khuyến mãi không được nhỏ hơn 0.',
            'variants.*.hinh_anh.array' => 'Danh sách ảnh biến thể phải là một mảng.',
            'variants.*.attributes.array' => 'Thuộc tính biến thể phải là mảng.',
            'variants.*.attributes.*.thuoc_tinh_id.required' => 'ID thuộc tính là bắt buộc.',
            'variants.*.attributes.*.thuoc_tinh_id.exists'   => 'Thuộc tính không tồn tại.',
            'variants.*.attributes.*.gia_tri.max' => 'Giá trị thuộc tính không vượt quá 255 ký tự.',
            'variants.*.attributes.*.gia_tri_thuoc_tinh_id.integer' => 'ID giá trị thuộc tính phải là số.',
            'variants.*.attributes.*.gia_tri_thuoc_tinh_id.exists'  => 'Giá trị thuộc tính không tồn tại.',
        ];
    }
}
