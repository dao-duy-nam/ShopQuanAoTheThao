<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('Role')->get();
        return response()->json($users, 200);
    }

 public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|string|min:6',
        'so_dien_thoai' => 'nullable|string|max:15',
        'vai_tro_id' => 'required|exists:vai_tros,id',
    ]);

    // Chỉ cho phép tạo tài khoản có vai trò là Admin (ví dụ: vai_tro_id = 1)
    if ($validated['vai_tro_id'] != 1) {
        return response()->json([
            'error' => 'Chỉ được phép tạo tài khoản với vai trò Admin.'
        ], 403); // Forbidden
    }

    $validated['password'] = bcrypt($validated['password']);
    $validated['trang_thai'] = 'active';

    $user = User::create($validated);

    return response()->json($user, 201);
}


        public function show($id)
    {
        $user = User::with('Role')->find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json($user, 200);
    }

public function updateStatus(Request $request, $id)
{
    $validated = $request->validate([
        'trang_thai' => 'required|in:active,inactive',
    ]);

    $user = User::findOrFail($id);

    // Chỉ cập nhật trường trạng thái
    $user->trang_thai = $validated['trang_thai'];
    $user->save();

    return response()->json([
        'message' => 'Cập nhật trạng thái thành công',
        'user' => $user,
    ], 200);
}


    // public function destroy($id)
    // {
    //     $user = User::findOrFail($id);
    //     $user->delete();

    //     return response()->json(['message' => 'User deleted successfully'], 200);
    // }
}
