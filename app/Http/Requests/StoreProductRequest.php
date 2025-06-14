<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Xác định người dùng có được phép gửi request không.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Quy tắc xác thực áp dụng cho request.
     */
    public function rules(): array
    {
        return [
            'ten'             => 'required|string|max:255',
            'mo_ta'           => 'nullable|string',
            'hinh_anh'        => 'nullable|string|url',
            'danh_muc_id'     => 'required|exists:danh_mucs,id',
            'gia'             => 'required|numeric|min:0',
            'variants'        => 'nullable|array',
            'variants.*.kich_co'        => 'required|string|max:100',
            'variants.*.mau_sac'        => 'required|string|max:100',
            'variants.*.so_luong'       => 'required|integer|min:0',
            'variants.*.gia'            => 'required|numeric|min:0',
            'variants.*.gia_khuyen_mai' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Thông báo lỗi tùy chỉnh cho các quy tắc xác thực.
     */
    public function messages(): array
    {
        return [
            // Sản phẩm
            'ten.required' => 'Vui lòng nhập tên sản phẩm.',
            'ten.string'   => 'Tên sản phẩm phải là một chuỗi ký tự.',
            'ten.max'      => 'Tên sản phẩm không được vượt quá 255 ký tự.',
            'mo_ta.string' => 'Mô tả sản phẩm phải là một chuỗi ký tự.',
            'hinh_anh.string' => 'Đường dẫn hình ảnh phải là một chuỗi ký tự.',
            'hinh_anh.url'    => 'Đường dẫn hình ảnh không hợp lệ.',
            'danh_muc_id.required' => 'Vui lòng chọn danh mục cho sản phẩm.',
            'danh_muc_id.exists'   => 'Danh mục đã chọn không tồn tại.',
            'gia.required' => 'Vui lòng nhập giá sản phẩm.',
            'gia.numeric'  => 'Giá sản phẩm phải là một số.',
            'gia.min'      => 'Giá sản phẩm không được nhỏ hơn 0.',

            // Biến thể
            'variants.array' => 'Trường biến thể phải là một mảng.',
            'variants.*.kich_co.required' => 'Vui lòng nhập kích cỡ cho mỗi biến thể.',
            'variants.*.kich_co.string'   => 'Kích cỡ biến thể phải là chuỗi ký tự.',
            'variants.*.kich_co.max'      => 'Kích cỡ biến thể không được vượt quá 100 ký tự.',
            'variants.*.mau_sac.required' => 'Vui lòng nhập màu sắc cho mỗi biến thể.',
            'variants.*.mau_sac.string'   => 'Màu sắc biến thể phải là chuỗi ký tự.',
            'variants.*.mau_sac.max'      => 'Màu sắc biến thể không được vượt quá 100 ký tự.',
            'variants.*.so_luong.required' => 'Vui lòng nhập số lượng cho mỗi biến thể.',
            'variants.*.so_luong.integer'  => 'Số lượng biến thể phải là số nguyên.',
            'variants.*.so_luong.min'      => 'Số lượng biến thể không được nhỏ hơn 0.',
            'variants.*.gia.required' => 'Vui lòng nhập giá cho mỗi biến thể.',
            'variants.*.gia.numeric'  => 'Giá biến thể phải là một số.',
            'variants.*.gia.min'      => 'Giá biến thể không được nhỏ hơn 0.',
            'variants.*.gia_khuyen_mai.numeric' => 'Giá khuyến mãi phải là một số.',
            'variants.*.gia_khuyen_mai.min'     => 'Giá khuyến mãi không được nhỏ hơn 0.',
        ];
    }
}
