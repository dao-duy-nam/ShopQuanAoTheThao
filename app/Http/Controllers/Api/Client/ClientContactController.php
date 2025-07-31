<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\Validator;

class ClientContactController extends Controller
{
    // 1. Gửi liên hệ
    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'email'   => 'required|email',
            'phone'   => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'type'    => 'required|in:khieu_nai,gop_y,hop_tac,can_ho_tro', // tùy chỉnh các loại
        ]);

        if ($validated->fails()) {
            return response()->json(['errors' => $validated->errors()], 422);
        }

        $contact = Contact::create([
            ...$validated->validated(),
            'status' => 'chua_xu_ly',
        ]);

        return response()->json(['message' => 'Gửi liên hệ thành công', 'data' => $contact], 201);
    }

    // 2. Lấy các loại liên hệ (tuỳ chọn nếu bạn muốn hiển thị list types)
    public function contactTypes()
    {
        return response()->json([
            'types' => [
                'khieu_nai'   => 'Khiếu nại',
                'gop_y'       => 'Góp ý',
                'hop_tac'     => 'Hợp tác',
                'can_ho_tro'  => 'Cần hỗ trợ'
            ]
        ]);
    }
}
