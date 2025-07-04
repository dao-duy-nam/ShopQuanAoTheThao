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
            'attributes.*.attribute_value_id' => 'nullable|required_without:attributes.*.gia_tri|integer|exists:gia_tri_thuoc_tinhs,id',
            'attributes.*.gia_tri' => 'nullable|required_without:attributes.*.attribute_value_id|string|max:255',
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

            'attributes.array' => 'Danh sách thuộc tính không hợp lệ.',
            'attributes.min' => 'Phải có ít nhất một thuộc tính.',
            'attributes.*.thuoc_tinh_id.required_with' => 'ID thuộc tính là bắt buộc khi cập nhật thuộc tính.',
            'attributes.*.thuoc_tinh_id.integer' => 'ID thuộc tính phải là số nguyên.',
            'attributes.*.thuoc_tinh_id.exists' => 'ID thuộc tính không tồn tại.',

            'attributes.*.attribute_value_id.required_without' => 'Bạn phải chọn giá trị có sẵn hoặc nhập giá trị mới.',
            'attributes.*.attribute_value_id.integer' => 'ID giá trị thuộc tính phải là số nguyên.',
            'attributes.*.attribute_value_id.exists' => 'Giá trị thuộc tính đã chọn không tồn tại.',

            'attributes.*.gia_tri.required_without' => 'Bạn phải nhập giá trị mới hoặc chọn giá trị có sẵn.',
            'attributes.*.gia_tri.string' => 'Giá trị thuộc tính phải là chuỗi.',
            'attributes.*.gia_tri.max' => 'Giá trị thuộc tính không được vượt quá 255 ký tự.',
        ];
    }
}
