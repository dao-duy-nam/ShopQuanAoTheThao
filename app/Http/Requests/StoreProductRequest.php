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
            'ten'             => 'required|string|max:255|unique:san_phams,ten',
            'mo_ta'           => 'nullable|string',
            'hinh_anh'        => 'nullable|array',
            'hinh_anh.*'      => 'image|mimes:jpg,jpeg,png,webp',
            'danh_muc_id'     => 'required|exists:danh_mucs,id',
            'gia'             => 'required|numeric|min:0',
            'gia_khuyen_mai'  => 'nullable|numeric|min:0',
            'so_luong'        => 'required_without:variants|integer|min:0',
            'variants'                     => 'nullable|array',
            'variants.*.so_luong'          => 'required_with:variants|integer|min:0',
            'variants.*.gia'               => 'required_with:variants|numeric|min:0',
            'variants.*.gia_khuyen_mai'    => 'nullable|numeric|min:0',
            'variants.*.hinh_anh'          => 'nullable|image|mimes:jpg,jpeg,png,webp',
            'variants.*.attributes' => 'nullable|array',
            'variants.*.attributes.*.thuoc_tinh_id' => 'required|integer|exists:thuoc_tinhs,id',
            'variants.*.attributes.*.gia_tri'       => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            // Sản phẩm chính
            'ten.required'       => 'Vui lòng nhập tên sản phẩm.',
            'ten.unique'         => 'Tên sản phẩm đã tồn tại, vui lòng chọn tên khác.',
            'ten.string'         => 'Tên sản phẩm phải là một chuỗi ký tự.',
            'ten.max'            => 'Tên sản phẩm không được vượt quá 255 ký tự.',
            'mo_ta.string'       => 'Mô tả sản phẩm phải là một chuỗi ký tự.',
            'hinh_anh.array'     => 'Hình ảnh phải là một mảng.',
            'hinh_anh.*.image'   => 'Mỗi hình ảnh phải là một tệp hình ảnh.',
            'hinh_anh.*.mimes'   => 'Hình ảnh chỉ chấp nhận định dạng jpg, jpeg, png, webp.',
            'danh_muc_id.required' => 'Vui lòng chọn danh mục cho sản phẩm.',
            'danh_muc_id.exists'   => 'Danh mục đã chọn không tồn tại.',
            'gia.required'       => 'Vui lòng nhập giá sản phẩm.',
            'gia.numeric'        => 'Giá sản phẩm phải là một số.',
            'gia.min'            => 'Giá sản phẩm không được nhỏ hơn 0.',
            'gia_khuyen_mai.numeric' => 'Giá khuyến mãi phải là một số.',
            'gia_khuyen_mai.min'     => 'Giá khuyến mãi không được nhỏ hơn 0.',
            'so_luong.required_without' => 'Vui lòng nhập số lượng nếu không có biến thể.',
            'so_luong.integer'          => 'Số lượng sản phẩm phải là số nguyên.',
            'so_luong.min'              => 'Số lượng sản phẩm không được nhỏ hơn 0.',

            // Biến thể
            'variants.array' => 'Trường biến thể phải là một mảng.',
            'variants.*.so_luong.required_with' => 'Vui lòng nhập số lượng cho mỗi biến thể.',
            'variants.*.so_luong.integer'       => 'Số lượng biến thể phải là số nguyên.',
            'variants.*.so_luong.min'           => 'Số lượng biến thể không được nhỏ hơn 0.',
            'variants.*.gia.required_with' => 'Vui lòng nhập giá cho mỗi biến thể.',
            'variants.*.gia.numeric'      => 'Giá biến thể phải là một số.',
            'variants.*.gia.min'          => 'Giá biến thể không được nhỏ hơn 0.',
            'variants.*.gia_khuyen_mai.numeric' => 'Giá khuyến mãi phải là một số.',
            'variants.*.gia_khuyen_mai.min'     => 'Giá khuyến mãi không được nhỏ hơn 0.',
            'variants.*.hinh_anh.image'   => 'Ảnh biến thể phải là một tệp hình ảnh.',
            'variants.*.hinh_anh.mimes'   => 'Ảnh biến thể chỉ chấp nhận định dạng jpg, jpeg, png, webp.',
            'variants.*.attributes.array' => 'Thuộc tính biến thể phải là một mảng.',
            'variants.*.attributes.*.thuoc_tinh_id.required' => 'ID thuộc tính là bắt buộc.',
            'variants.*.attributes.*.thuoc_tinh_id.exists' => 'ID thuộc tính không tồn tại trong cơ sở dữ liệu.',
            'variants.*.attributes.*.gia_tri.required' => 'Giá trị thuộc tính là bắt buộc.',
            'variants.*.attributes.*.gia_tri.string' => 'Giá trị thuộc tính phải là một chuỗi ký tự.',
            'variants.*.attributes.*.gia_tri.max' => 'Giá trị thuộc tính không được vượt quá 255 ký tự.',
        ];
    }
}
