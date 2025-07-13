<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDiscountCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ma' => 'required|string|unique:ma_giam_gias,ma',
            'ten' => 'nullable|string',
            'loai' => 'required|in:phan_tram,tien',
            'ap_dung_cho' => 'required|in:toan_don,san_pham',
            'san_pham_id' => 'required_if:ap_dung_cho,san_pham|exists:san_phams,id',
            'gia_tri' => 'required|integer|min:1',
            'gia_tri_don_hang' => 'nullable|integer|min:0',
            'so_luong' => 'required|integer|min:0',
            'gioi_han' => 'nullable|integer|min:0',
            'ngay_bat_dau' => 'nullable|date',
            'ngay_ket_thuc' => 'nullable|date|after:ngay_bat_dau',
            'trang_thai' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'ma.required' => 'Vui lòng nhập mã giảm giá.',
            'ma.unique' => 'Mã giảm giá đã tồn tại.',
            'loai.required' => 'Vui lòng chọn loại giảm giá.',
            'loai.in' => 'Loại giảm giá không hợp lệ.',
            'ap_dung_cho.required' => 'Vui lòng chọn phạm vi áp dụng.',
            'ap_dung_cho.in' => 'Phạm vi áp dụng không hợp lệ.',
            'san_pham_id.exists' => 'Sản phẩm áp dụng không hợp lệ.',
            'gia_tri.required' => 'Vui lòng nhập giá trị giảm.',
            'gia_tri.integer' => 'Giá trị giảm phải là số nguyên.',
            'gia_tri.min' => 'Giá trị giảm phải lớn hơn 0.',
            'gia_tri_don_hang.integer' => 'Giá trị đơn hàng phải là số.',
            'gia_tri_don_hang.min' => 'Giá trị đơn hàng tối thiểu phải từ 0 trở lên.',
            'so_luong.required' => 'Vui lòng nhập số lượng mã.',
            'so_luong.integer' => 'Số lượng phải là số nguyên.',
            'so_luong.min' => 'Số lượng phải từ 0 trở lên.',
            'gioi_han.integer' => 'Giới hạn phải là số nguyên.',
            'gioi_han.min' => 'Giới hạn phải từ 0 trở lên.',
            'san_pham_id.required_if' => 'Vui lòng chọn sản phẩm áp dụng nếu phạm vi là sản phẩm',
            'ngay_bat_dau.date' => 'Ngày bắt đầu không hợp lệ.',
            'ngay_ket_thuc.date' => 'Ngày kết thúc không hợp lệ.',
            'ngay_ket_thuc.after' => 'Ngày kết thúc phải sau ngày bắt đầu.',
            'trang_thai.boolean' => 'Trạng thái không hợp lệ.',
        ];
    }
}
