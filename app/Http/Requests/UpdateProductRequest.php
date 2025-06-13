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
            'variants'        => 'required|array|min:1',
            'variants.*.id'   => 'nullable|integer|exists:bien_thes,id',
            'variants.*.kich_co'        => 'required|string|max:100',
            'variants.*.mau_sac'        => 'required|string|max:100',
            'variants.*.so_luong'       => 'required|integer|min:0',
            'variants.*.gia'            => 'required|numeric|min:0',
            'variants.*.gia_khuyen_mai' => 'nullable|numeric|min:0',
            'deleted_variant_ids'       => 'nullable|array',
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
            'variants.required' => 'Phải có ít nhất một biến thể.',
            'variants.array' => 'Biến thể phải là một mảng.',
            'variants.*.id.integer' => 'ID biến thể phải là số nguyên.',
            'variants.*.id.exists' => 'ID biến thể không tồn tại.',
            'variants.*.kich_co.required' => 'Kích cỡ không được để trống.',
            'variants.*.kich_co.string' => 'Kích cỡ phải là chuỗi.',
            'variants.*.kich_co.max' => 'Kích cỡ không được vượt quá 100 ký tự.',
            'variants.*.mau_sac.required' => 'Màu sắc không được để trống.',
            'variants.*.mau_sac.string' => 'Màu sắc phải là chuỗi.',
            'variants.*.mau_sac.max' => 'Màu sắc không được vượt quá 100 ký tự.',
            'variants.*.so_luong.required' => 'Số lượng của biến thể không được để trống.',
            'variants.*.so_luong.integer' => 'Số lượng của biến thể phải là số nguyên.',
            'variants.*.so_luong.min' => 'Số lượng của biến thể phải lớn hơn hoặc bằng 0.',
            'variants.*.gia.required' => 'Giá của biến thể không được để trống.',
            'variants.*.gia.numeric' => 'Giá của biến thể phải là số.',
            'variants.*.gia.min' => 'Giá của biến thể phải lớn hơn hoặc bằng 0.',
            'variants.*.gia_khuyen_mai.numeric' => 'Giá khuyến mãi của biến thể phải là số.',
            'variants.*.gia_khuyen_mai.min' => 'Giá khuyến mãi của biến thể phải lớn hơn hoặc bằng 0.',
            'deleted_variant_ids.array' => 'Danh sách biến thể xóa phải là mảng.',
            'deleted_variant_ids.*.integer' => 'ID biến thể xóa phải là số nguyên.',
        ];
    }
}
