<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\User;
use App\Models\DiscountCode;
use Illuminate\Http\Request;
use App\Mail\DiscountCodeMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\DiscountCodeResource;
use App\Http\Requests\StoreDiscountCodeRequest;
use App\Http\Requests\UpdateDiscountCodeRequest;

class DiscountCodeController extends Controller
{

    public function index(Request $request)
    {
        $query = DiscountCode::query();

        if ($request->has('keyword')) {
            $query->where('ma', 'like', '%' . $request->keyword . '%')
                ->orWhere('ten', 'like', '%' . $request->keyword . '%');
        }

        $codes = $query->latest()->paginate(10);
        return DiscountCodeResource::collection($codes);
    }


    public function store(StoreDiscountCodeRequest $request)
    {
        $code = DiscountCode::create($request->validated());

        return response()->json([
            'message' => 'Tạo mã giảm giá thành công.',
            'data' => new DiscountCodeResource($code),
        ]);
    }


    public function show($id)
    {
        $code = DiscountCode::with('product')->findOrFail($id);
        return new DiscountCodeResource($code);
    }

    public function update(UpdateDiscountCodeRequest $request, $id)
    {
        $code = DiscountCode::findOrFail($id);
        $code->update($request->validated());

        return response()->json([
            'message' => 'Cập nhật mã giảm giá thành công.',
            'data' => new DiscountCodeResource($code),
        ]);
    }


    public function changeStatus($id, Request $request)
    {
        $data = $request->validate([
            'trang_thai' => 'required|boolean',
        ], [
            'trang_thai.required' => 'Vui lòng chọn trạng thái.',
            'trang_thai.boolean' => 'Trạng thái không hợp lệ.',
        ]);

        $code = DiscountCode::findOrFail($id);
        $code->update(['trang_thai' => $data['trang_thai']]);

        return response()->json([
            'message' => 'Cập nhật trạng thái thành công.',
            'data' => new DiscountCodeResource($code),
        ]);
    }


    public function destroy($id)
    {
        $code = DiscountCode::findOrFail($id);
        $code->delete();

        return response()->json([
            'message' => 'Đã xoá mềm mã giảm giá.',
        ]);
    }


    public function trash()
    {
        $trashed = DiscountCode::onlyTrashed()->latest()->paginate(10);
        return DiscountCodeResource::collection($trashed);
    }


    public function restore($id)
    {
        $code = DiscountCode::onlyTrashed()->findOrFail($id);
        $code->restore();

        return response()->json([
            'message' => 'Khôi phục mã giảm giá thành công.',
            'data' => new DiscountCodeResource($code),
        ]);
    }

    public function sendToUsers(Request $request, $id)
    {
        $request->validate([
            'kieu' => 'required|in:tat_ca,ngau_nhien',
            'so_luong' => 'nullable|integer|min:1',
        ], [
            'kieu.required' => 'Vui lòng chọn kiểu gửi.',
            'kieu.in' => 'Kiểu gửi không hợp lệ.',
            'so_luong.integer' => 'Số lượng phải là số nguyên.',
            'so_luong.min' => 'Số lượng ít nhất phải là 1.',
        ]);

        $code = DiscountCode::findOrFail($id);
        $users = collect();

        if ($request->kieu === 'tat_ca') {
            $users = User::where('vai_tro_id', User::ROLE_USER)
                ->whereNotNull('email')
                ->get();
        } elseif ($request->kieu === 'ngau_nhien') {

            $tongUser = User::where('vai_tro_id', User::ROLE_USER)
                ->whereNotNull('email')
                ->count();

            $soLuong = $request->input('so_luong', rand(1, min(10, $tongUser)));

            $users = User::where('vai_tro_id', User::ROLE_USER)
                ->whereNotNull('email')
                ->inRandomOrder()
                ->limit($soLuong)
                ->get();
        }

        foreach ($users as $user) {
            Mail::to($user->email)->queue(new DiscountCodeMail($user, $code));
        }

        return response()->json([
            'message' => 'Đã gửi mã giảm giá thành công.',
            'so_luong_gui' => $users->count(),
        ]);
    }
}
