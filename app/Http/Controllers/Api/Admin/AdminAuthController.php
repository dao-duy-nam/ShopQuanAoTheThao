<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{

    public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => ['required', 'string', 'email', 'regex:/^\S+@[a-zA-Z]+[a-zA-Z0-9.-]*\.[a-z]{2,}$/'],
        'password' => ['required', 'string', 'min:6'],
    ], [
        'email.required' => 'Email không được để trống.',
        'email.email' => 'Email không đúng định dạng.',
        'email.regex' => 'Email không đúng định dạng hoặc domain không hợp lệ.',
        'password.required' => 'Mật khẩu không được để trống.',
        'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
    ]);

    $user = \App\Models\User::where('email', $credentials['email'])->first();

    if (!$user || !Hash::check($credentials['password'], $user->password)) {
        return response()->json(['message' => 'Đăng nhập thất bại, kiểm tra lại email hoặc mật khẩu'], 401);
    }

    if (!$user->isRoleAdmin() && !$user->isRoleStaff()) {
        return response()->json(['message' => 'Tài khoản không có quyền truy cập khu vực quản trị.'], 403);
    }

    $token = $user->createToken('admin-token')->plainTextToken;

    return response()->json([
        'message' => 'Đăng nhập thành công',
        'token' => $token,
        'user' => $user,
    ]);
}



    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Đăng xuất thành công',
        ]);
    }
}
