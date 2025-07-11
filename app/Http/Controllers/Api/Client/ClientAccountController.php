<?php

namespace App\Http\Controllers\Api\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserProfileResource;

class ClientAccountController extends Controller
{
    public function profile(Request $request)
    {
        $user = $request->user()->load(['diaChis' => function ($q) {
            $q->where('mac_dinh', true);
        }]);

        return response()->json(new UserProfileResource($user));
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'anh_dai_dien' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'tinh_thanh' => 'nullable|string|max:255',
            'quan_huyen' => 'nullable|string|max:255',
            'phuong_xa' => 'nullable|string|max:255',
            'dia_chi_chi_tiet' => 'nullable|string|max:255',
            'gioi_tinh' => 'nullable|in:nam,nu,khac',
            'ngay_sinh' => 'nullable|date',
        ]);

        if ($request->hasFile('anh_dai_dien')) {
            $path = $request->file('anh_dai_dien')->store('anh_dai_dien', 'public');
            $data['anh_dai_dien'] = $path;
        }

        $user->update([
            'name' => $data['name'],
            'so_dien_thoai' => $data['phone'] ?? $user->so_dien_thoai,
            'anh_dai_dien' => $data['anh_dai_dien'] ?? $user->anh_dai_dien,
            'gioi_tinh' => $data['gioi_tinh'] ?? $user->gioi_tinh,
            'ngay_sinh' => $data['ngay_sinh'] ?? $user->ngay_sinh,
        ]);

        if ($data['tinh_thanh'] ?? null && $data['quan_huyen'] ?? null && $data['phuong_xa'] ?? null) {
            $addressData = [
                'tinh_thanh' => $data['tinh_thanh'],
                'quan_huyen' => $data['quan_huyen'],
                'phuong_xa' => $data['phuong_xa'],
                'dia_chi_chi_tiet' => $data['dia_chi_chi_tiet'] ?? null,
                'mac_dinh' => true,
            ];
            $defaultAddress = $user->diaChis()->where('mac_dinh', true)->first();
            if ($defaultAddress) {
                $defaultAddress->update($addressData);
            } else {
                $user->diaChis()->create($addressData);
            }
        }

        $user->load(['diaChis' => function ($q) {
            $q->where('mac_dinh', true);
        }]);

        return response()->json(new UserProfileResource($user));
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Sai mật khẩu hiện tại'], 403);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json(['message' => 'Đổi mật khẩu thành công']);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Đăng xuất thành công']);
    }
}
