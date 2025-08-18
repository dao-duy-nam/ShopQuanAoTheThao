<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\UserBlockedMail;
use App\Mail\UserUnblockedMail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\UserAdminResource;

class UserController extends Controller
{
    public function filterAllUsers(Request $request)
    {
        $query = User::with('role');

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        if ($request->filled('so_dien_thoai')) {
            $query->where('so_dien_thoai', 'like', '%' . $request->so_dien_thoai . '%');
        }

        if ($request->filled('gioi_tinh')) {
            $query->where('gioi_tinh', $request->gioi_tinh);
        }

        if ($request->filled('ngay_sinh')) {
            $query->whereDate('ngay_sinh', $request->ngay_sinh);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('ngay_sinh', [$request->from_date, $request->to_date]);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'message' => 'Danh sách người dùng lọc chung',
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

    public function listAdmins(Request $request)
    {
        return $this->getUsersByRoleNames(['admin'], $request);
    }

    public function listStaffs(Request $request)
    {
        return $this->getUsersByRoleNames(['staff'], $request);
    }

    public function listCustomers(Request $request)
    {
        return $this->getUsersByRoleNames(['user'], $request);
    }

    protected function getUsersByRoleNames(array $roleNames, Request $request)
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
            ->whereIn('vai_tros.ten_vai_tro', $roleNames);

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('users.name', 'like', "%$keyword%")
                    ->orWhere('users.email', 'like', "%$keyword%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'message' => 'Danh sách người dùng theo vai trò: ' . implode(', ', $roleNames),
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


        $staffRole = Role::where('ten_vai_tro', 'staff')->first();
        if (!$staffRole) {
            return response()->json([
                'message' => 'Vai trò staff chưa tồn tại trong hệ thống'
            ], 500);
        }

        $data['vai_tro_id'] = $staffRole->id;

        $user = User::create($data);
        $user->load('role');

        return response()->json([
            'message' => 'Tạo tài khoản staff thành công',
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
            'message' => 'Tài khoản đã bị khóa',
            'status' => 200,
            'data' => new UserAdminResource($user),
        ]);
    }

    public function block(Request $request, $id)
    {
        $request->validate([
            'ly_do_block' => 'nullable|string',
        ]);

        $user = User::with('role')->findOrFail($id);

        if ($user->role && $user->role->ten_vai_tro === 'admin') {
            return response()->json([
                'message' => 'Không thể khóa tài khoản admin',
            ], 403);
        }

        $user->update([
            'trang_thai' => 'blocked',
            'ly_do_block' => $request->ly_do_block,
            'block_den_ngay' => null,
            'kieu_block' => $request->kieu_block ?: 'đã khoá!!', // nếu không truyền thì gán mặc định
        ]);

        Mail::to($user->email)->queue(new UserBlockedMail($user, $request->ly_do_block));

        return response()->json([
            'message' => 'Tài khoản đã bị khóa',
            'status' => 200,
            'data' => new UserAdminResource($user),
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

        Mail::to($user->email)->queue(new UserUnblockedMail($user));

        return response()->json([
            'message' => 'Tài khoản đã mở',
            'status' => 200,
            'data' => new UserAdminResource($user),
        ]);
    }


    public function show($id)
    {
        $authUser = Auth::user();
        $user = User::with('role')->findOrFail($id);

        if ($authUser && $authUser->role && $authUser->role->ten_vai_tro === 'staff' && $user->role && $user->role->ten_vai_tro === 'admin') {
            return response()->json([
                'message' => 'Bạn không có quyền xem chi tiết tài khoản admin',
            ], 403);
        }

        if ($user->role && $user->role->ten_vai_tro === 'user') {
            $user->load('diaChis');
        }

        $data = $user->toArray();
        $data['gioi_tinh'] = $user->gioi_tinh;

        return response()->json([
            'message' => 'Chi tiết người dùng',
            'status' => 200,
            'data' => $data
        ], 200);
    }
}
