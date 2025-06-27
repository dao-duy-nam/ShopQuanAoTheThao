<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartQuantityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'so_luong' => 'required|integer|min:1',
            'action' => 'nullable|in:replace,add',
        ];
    }

    public function messages(): array
    {
        return [
            'so_luong.required' => 'Vui lòng nhập số lượng',
            'so_luong.integer' => 'Số lượng phải là số nguyên',
            'so_luong.min' => 'Số lượng phải lớn hơn 0',
            'action.in' => 'Action phải là replace hoặc add',
        ];
    }
} 