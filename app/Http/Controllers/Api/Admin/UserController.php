<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('users')
            ->leftJoin('vai_tros', 'users.vai_tro_id', '=', 'vai_tros.id')
            ->select(
                'users.id', 'users.name', 'users.email', 'users.so_dien_thoai',
                'users.anh_dai_dien', 'users.trang_thai',
                'users.vai_tro_id', 'vai_tros.ten_vai_tro', 'users.created_at', 'users.updated_at'
            );

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('users.name', 'like', "%$keyword%")
                  ->orWhere('vai_tros.ten_vai_tro', 'like', "%$keyword%");
            });
        }

        $users = $query->paginate(10);

        return response()->json([
            'message' => 'Danh sách người dùng',
            'status' => 200,
            'data' => $users->items(),  // Trả về mảng dữ liệu người dùng
            'pagination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
            ],
        ], 200);
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
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['trang_thai'] = 'active';

        // Dùng Eloquent tạo User mới
        $user = User::create($data);

        // Load relation vai tro (role)
        $user->load('role');

        return response()->json([
            'message' => 'Tạo tài khoản thành công',
            'status' => 201,
            'data' => $user->toArray()
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
        'kieu_block' => 'nullable|in:vinh_vien,7_ngay,1_ngay',
    ]);

    $user = User::findOrFail($id);

    $kieuBlock = $request->kieu_block ?? '1_ngay'; // Nếu không truyền thì mặc định là 1 ngày

    $blockDenNgay = match ($kieuBlock) {
        '1_ngay' => now()->addDay(),
        '7_ngay' => now()->addDays(7),
        'vinh_vien' => null,
    };

    $user->trang_thai = 'blocked';
    $user->ly_do_block = $request->ly_do_block;
    $user->block_den_ngay = $blockDenNgay;
    $user->kieu_block = $kieuBlock;
    $user->save();

    return response()->json([
        'message' => 'Tài khoản đã bị khóa',
        'status' => 200,
        'data' => $user->only([
            'id', 'name', 'email', 'trang_thai', 'kieu_block', 'block_den_ngay', 'ly_do_block'
        ])
    ], 200);
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
