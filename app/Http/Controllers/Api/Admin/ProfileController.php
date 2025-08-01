<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = Auth::user(); // lấy user đăng nhập

        if ($request->isMethod('get')) {
            return response()->json($user, 200);
        }

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'so_dien_thoai' => 'nullable|string|max:20',
                'gioi_tinh' => 'nullable|in:nam,nu,khac',
                'ngay_sinh' => 'nullable|date',
                'password' => 'nullable|min:6|confirmed',
                'anh_dai_dien' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
            ]);

            // Khởi tạo dữ liệu để cập nhật
            $data = $request->only([
                'name', 'email', 'so_dien_thoai', 'gioi_tinh', 'ngay_sinh'
            ]);

            // Nếu có password thì mã hóa
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            // Nếu có upload ảnh
            if ($request->hasFile('anh_dai_dien')) {
                $file = $request->file('anh_dai_dien');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('avatars', $filename, 'public');
                $data['anh_dai_dien'] = $path;
            }

            // Cập nhật
            $user->update($data);

            return response()->json([
                'message' => 'Cập nhật thông tin thành công!',
                'user' => $user
            ], 200);
        }

        return response()->json(['message' => 'Method không hợp lệ'], 405);
    }
}
