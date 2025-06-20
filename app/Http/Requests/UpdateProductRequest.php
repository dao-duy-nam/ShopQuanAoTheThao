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
        $productId = $this->route('id'); // Lấy ID từ route

        return [
            'ten' => 'required|string|max:255|unique:san_phams,ten,' . $productId,
            'mo_ta'              => 'nullable|string',
            'hinh_anh'           => 'nullable|array',
            'hinh_anh.*'         => 'image|mimes:jpg,jpeg,png,webp',
            'danh_muc_id'        => 'required|exists:danh_mucs,id',
            'gia'                => 'required|numeric|min:0',
            'so_luong'           => 'required_without:variants|integer|min:0',
            'gia_khuyen_mai'     => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            // Sản phẩm
            'ten.required'       => 'Vui lòng nhập tên sản phẩm.',
            'ten.unique'         => 'Tên sản phẩm đã tồn tại, vui lòng chọn tên khác.',
            'ten.string'         => 'Tên sản phẩm phải là chuỗi ký tự.',
            'ten.max'            => 'Tên sản phẩm không được vượt quá 255 ký tự.',
            'mo_ta.string'       => 'Mô tả phải là chuỗi ký tự.',
            'hinh_anh.array'     => 'Hình ảnh phải là một mảng.',
            'hinh_anh.*.image'   => 'Mỗi hình ảnh phải là tệp hình ảnh.',
            'hinh_anh.*.mimes'   => 'Hình ảnh chỉ chấp nhận định dạng jpg, jpeg, png, webp.',
            'danh_muc_id.required' => 'Vui lòng chọn danh mục.',
            'danh_muc_id.exists'   => 'Danh mục đã chọn không tồn tại.',
            'gia.required'       => 'Vui lòng nhập giá sản phẩm.',
            'gia.numeric'        => 'Giá phải là số.',
            'gia.min'            => 'Giá không được nhỏ hơn 0.',
            'gia_khuyen_mai.numeric' => 'Giá khuyến mãi phải là số.',
            'gia_khuyen_mai.min'     => 'Giá khuyến mãi không được nhỏ hơn 0.',
            'so_luong.required_without' => 'Vui lòng nhập số lượng nếu không có biến thể.',
            'so_luong.integer'   => 'Số lượng phải là số nguyên.',
            'so_luong.min'       => 'Số lượng không được nhỏ hơn 0.',
        ];
    }
}
