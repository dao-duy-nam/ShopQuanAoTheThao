<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttributeValue;
use Illuminate\Http\Request;

class AttributeValueController extends Controller
{
    public function index()
    {
        $data = AttributeValue::with(['attribute', 'variants'])->get();

        return response()->json([
            'message' => 'Lấy danh sách giá trị thuộc tính thành công',
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'gia_tri' => 'required|string|max:255',
            'thuoc_tinh_id' => 'required|exists:thuoc_tinhs,id',
        ]);

        $value = AttributeValue::create($validated);

        return response()->json([
            'message' => 'Thêm giá trị thuộc tính thành công',
            'data' => $value
        ], 200);
    }

    public function show($id)
    {
        $value = AttributeValue::with(['attribute', 'variants'])->find($id);

        if (!$value) {
            return response()->json([
                'message' => 'Không tìm thấy giá trị thuộc tính',
                'data' => null
            ], 404);
        }

        return response()->json([
            'message' => 'Lấy chi tiết giá trị thuộc tính thành công',
            'data' => $value
        ]);
    }

    public function update(Request $request, $id)
    {
        $value = AttributeValue::find($id);

        if (!$value) {
            return response()->json([
                'message' => 'Không tìm thấy giá trị thuộc tính',
                'data' => null
            ], 404);
        }

        $validated = $request->validate([
            'gia_tri' => 'required|string|max:255',
            'thuoc_tinh_id' => 'required|exists:thuoc_tinhs,id',
        ]);

        $value->update($validated);

        return response()->json([
            'message' => 'Cập nhật giá trị thuộc tính thành công',
            'data' => $value
        ]);
    }

    public function destroy($id)
    {
        $value = AttributeValue::with('variants')->find($id);

        if (!$value) {
            return response()->json([
                'message' => 'Không tìm thấy giá trị thuộc tính',
                'data' => null
            ], 404);
        }

        if ($value->variants->count() > 0) {
            return response()->json([
                'message' => 'Không thể xóa vì đang được sử dụng trong biến thể sản phẩm',
                'data' => null
            ], 422);
        }

        $value->delete();

        return response()->json([
            'message' => 'Xóa mềm giá trị thuộc tính thành công',
            'data' => null
        ]);
    }

    public function trash()
    {
        $trashed = AttributeValue::onlyTrashed()->with('attribute')->get();

        return response()->json([
            'message' => 'Danh sách giá trị thuộc tính đã bị xóa',
            'data' => $trashed
        ]);
    }

    public function restore($id)
    {
        $value = AttributeValue::onlyTrashed()->find($id);

        if (!$value) {
            return response()->json([
                'message' => 'Không tìm thấy giá trị thuộc tính đã xóa',
                'data' => null
            ], 404);
        }

        $value->restore();

        return response()->json([
            'message' => 'Khôi phục giá trị thuộc tính thành công',
            'data' => $value
        ]);
    }
}
