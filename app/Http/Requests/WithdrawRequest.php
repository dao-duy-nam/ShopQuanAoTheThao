<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'amount' => 'required|numeric|min:1000',
            'bank_account' => 'required|string',
            'bank_name' => 'required|string',
            'account_name' => 'required|string',
        ];
    }
} 