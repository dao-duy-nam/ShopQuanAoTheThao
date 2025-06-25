<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function listAdmins(Request $request)
    {
        return $this->getUsersByRoleName('admin', $request);
    }

    public function listCustomers(Request $request)
    {
        return $this->getUsersByRoleName('user', $request);
    }

    // Hàm phụ dùng chung
    protected function getUsersByRoleName(string $roleName, Request $request)
    {
        $query = DB::table('users')
            ->leftJoin('vai_tros', 'users.vai_tro_id', '=', 'vai_tros.id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.so_dien_thoai',
                'users.anh_dai_dien',
                'users.trang_thai',
                'users.vai_tro_id',
                'vai_tros.ten_vai_tro',
                'users.created_at',
                'users.updated_at'
            )
            ->where('vai_tros.ten_vai_tro', $roleName);

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('users.name', 'like', "%$keyword%")
                    ->orWhere('users.email', 'like', "%$keyword%");
            });
        }

        $users = $query->paginate(10);

        return response()->json([
            'message' => 'Danh sách người dùng theo vai trò: ' . $roleName,
            'status' => 200,
            'data' => $users->items(),
            'pagination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
            ],
        ]);
    }


    public function store(Request $request)
{
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6',
        'so_dien_thoai' => 'nullable|string',
        'vai_tro_id' => 'required|exists:vai_tros,id',
        'ngay_sinh' => 'required|date',
        'anh_dai_dien' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', 
    ]);

     if ($request->hasFile('anh_dai_dien')) {
        $path = $request->file('anh_dai_dien')->store('users', 'public');
        $data['anh_dai_dien'] = $path;
    }

    $data['password'] = Hash::make($data['password']);
    $data['trang_thai'] = 'active';
    $data['email_verified_at'] = now(); 

    $user = User::create($data);

    $user->load('role');

    return response()->json([
        'message' => 'Tạo tài khoản thành công',
        'status' => 201,
        'data' => $user
    ], 201);
}


    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'vai_tro_id' => 'required|exists:vai_tros,id'
        ]);

        $user = User::findOrFail($id);
        $user->vai_tro_id = $request->vai_tro_id;
        $user->save();

        $user->load('role');

        return response()->json([
            'message' => 'Cập nhật quyền thành công',
            'status' => 200,
            'data' => $user->toArray()
        ], 200);
    }

    public function block(Request $request, $id)
    {
        $request->validate([
            'ly_do_block' => 'required|string',
        ]);

        $user = User::findOrFail($id);

        $user->update([
            'trang_thai' => 'blocked',
            'ly_do_block' => $request->ly_do_block,
            'block_den_ngay' => null,
            'kieu_block' => 'vinh_vien',
        ]);

        return response()->json([
            'message' => 'Tài khoản đã bị khóa vĩnh viễn',
            'status' => 200,
            'data' => $user->only([
                'id',
                'name',
                'email',
                'trang_thai',
                'kieu_block',
                'block_den_ngay',
                'ly_do_block'
            ])
        ]);
    }
    public function unblock($id)
    {
        $user = User::findOrFail($id);

        if ($user->trang_thai !== 'blocked') {
            return response()->json([
                'message' => 'Tài khoản không bị khóa',
            ], 400);
        }

        $user->update([
            'trang_thai' => 'active',
            'ly_do_block' => null,
            'block_den_ngay' => null,
            'kieu_block' => null,
        ]);

        return response()->json([
            'message' => 'Tài khoản đã được mở khóa',
            'data' => $user->only(['id', 'name', 'email', 'trang_thai']),
        ]);
    }




    public function show($id)
    {
        $user = User::with('role')->findOrFail($id);

        return response()->json([
            'message' => 'Chi tiết người dùng',
            'status' => 200,
            'data' => $user->toArray()
        ], 200);
    }
}
