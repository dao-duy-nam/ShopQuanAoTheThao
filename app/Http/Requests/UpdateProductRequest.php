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
            'ten' => 'required|string|max:255|unique:san_phams,ten,' . $this->route('id') . ',id',
            'mo_ta'              => 'nullable|string',
            'hinh_anh'           => 'nullable|array',
            'hinh_anh.*'         => 'image|mimes:jpg,jpeg,png,webp',
            'danh_muc_id'        => 'required|exists:danh_mucs,id',
            'gia'                => 'required|numeric|min:0',
            'so_luong'           => 'required_without:variants|integer|min:0',
            'gia_khuyen_mai'     => 'nullable|numeric|min:0',
            'variants'           => 'nullable|array',
            'variants.*.kich_co' => 'required_with:variants|string|max:100',
            'variants.*.mau_sac' => 'required_with:variants|string|max:100',
            'variants.*.so_luong' => 'required_with:variants|integer|min:0',
            'variants.*.gia'     => 'required_with:variants|numeric|min:0',
            'variants.*.gia_khuyen_mai' => 'nullable|numeric|min:0',
            'variants.*.hinh_anh' => 'nullable|image|mimes:jpg,jpeg,png,webp',
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

            // Biến thể
            'variants.array'                          => 'Biến thể phải là một mảng.',
            'variants.*.kich_co.required_with'        => 'Vui lòng nhập kích cỡ cho biến thể.',
            'variants.*.kich_co.string'               => 'Kích cỡ phải là chuỗi ký tự.',
            'variants.*.kich_co.max'                  => 'Kích cỡ không vượt quá 100 ký tự.',
            'variants.*.mau_sac.required_with'        => 'Vui lòng nhập màu sắc cho biến thể.',
            'variants.*.mau_sac.string'               => 'Màu sắc phải là chuỗi ký tự.',
            'variants.*.mau_sac.max'                  => 'Màu sắc không vượt quá 100 ký tự.',
            'variants.*.so_luong.required_with'       => 'Vui lòng nhập số lượng cho biến thể.',
            'variants.*.so_luong.integer'             => 'Số lượng phải là số nguyên.',
            'variants.*.so_luong.min'                 => 'Số lượng không được nhỏ hơn 0.',
            'variants.*.gia.required_with'            => 'Vui lòng nhập giá cho biến thể.',
            'variants.*.gia.numeric'                  => 'Giá phải là số.',
            'variants.*.gia.min'                      => 'Giá không được nhỏ hơn 0.',
            'variants.*.gia_khuyen_mai.numeric'       => 'Giá khuyến mãi phải là số.',
            'variants.*.gia_khuyen_mai.min'           => 'Giá khuyến mãi không được nhỏ hơn 0.',
            'variants.*.hinh_anh.image'               => 'Ảnh phải là tệp hình ảnh.',
            'variants.*.hinh_anh.mimes'               => 'Ảnh chỉ chấp nhận định dạng jpg, jpeg, png, webp.',
        ];
    }
}
