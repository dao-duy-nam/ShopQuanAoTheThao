<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDiscountCodeRequest;
use App\Http\Requests\UpdateDiscountCodeRequest;
use App\Http\Resources\DiscountCodeResource;
use App\Models\DiscountCode;
use Illuminate\Http\Request;

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
}
