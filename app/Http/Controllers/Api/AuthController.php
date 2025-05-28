<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\LoginSuccessMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email|regex:/^\S+@[a-zA-Z]+[a-zA-Z0-9.-]*\.[a-z]{2,}$/',
            'password' => 'required|string|confirmed|min:6',
        ], [
            'name.required' => 'Tên không được để trống.',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email đã được đăng ký.',
            'email.regex' => 'Email không đúng định dạng hoặc domain không hợp lệ.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
        ]);


        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'vai_tro_id' => User::ROLE_USER
        ]);


        event(new Registered($user));

        return response()->json([
            'message' => 'Đăng ký thành công.'
        ]);
    }





    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|regex:/^\S+@[a-zA-Z]+[a-zA-Z0-9.-]*\.[a-z]{2,}$/',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không đúng định dạng.',
            'email.regex' => 'Email không đúng định dạng hoặc domain không hợp lệ.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
        ]);


        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Thông tin đăng nhập không chính xác.'],
            ]);
        }


        $token = $user->createToken('auth_token')->plainTextToken;


        Mail::to($user->email)->send(new LoginSuccessMail($user, now()));

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'vai_tro_id' => $user->vai_tro_id,
            'user' => $user,
            'message' => 'Đăng nhập thành công.',
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Đăng xuất thành công']);
    }
}
