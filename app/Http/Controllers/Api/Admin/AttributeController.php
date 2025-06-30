<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::all();

        return response()->json([
            'status' => 'success',
            'data' => $attributes,
        ]);
    }
    public function show($id)
    {
        $attribute = Attribute::withTrashed()->with('values')->find($id);
        if (!$attribute) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy thuộc tính.',
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'data' => $attribute,
        ]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ten' => 'required|string|max:255',
        ]);

        $exists = Attribute::whereRaw('LOWER(ten) = ?', [strtolower(trim($validated['ten']))])->exists();
        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Thuộc tính này đã tồn tại.',
            ], 409);
        }

        $attribute = Attribute::create([
            'ten' => trim($validated['ten']),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Tạo thuộc tính thành công.',
            'data' => $attribute,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'ten' => 'required|string|max:255',
        ]);

        $attribute = Attribute::findOrFail($id);

        $exists = Attribute::whereRaw('LOWER(ten) = ?', [strtolower(trim($validated['ten']))])
            ->where('id', '!=', $id)
            ->exists();
        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tên thuộc tính đã được sử dụng.',
            ], 409);
        }

        $attribute->update([
            'ten' => trim($validated['ten']),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật thuộc tính thành công.',
            'data' => $attribute,
        ]);
    }

    public function destroy($id)
    {
        $attribute = Attribute::findOrFail($id);

        // Có liên kết giá trị đang dùng?
        if ($attribute->Values()->whereHas('variants')->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể xóa thuộc tính vì giá trị đang được sử dụng trong biến thể.',
            ], 409);
        }

        $attribute->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Xóa mềm thuộc tính thành công.',
        ]);
    }

    public function trashed()
    {
        $trashed = Attribute::onlyTrashed()->get();

        return response()->json([
            'status' => 'success',
            'data' => $trashed,
        ]);
    }

    public function restore($id)
    {
        $attribute = Attribute::onlyTrashed()->findOrFail($id);
        $attribute->restore();

        return response()->json([
            'status' => 'success',
            'message' => 'Khôi phục thuộc tính thành công.',
        ]);
    }

    public function forceDelete($id)
    {
        $attribute = Attribute::onlyTrashed()->findOrFail($id);

        if ($attribute->Values()->whereHas('variants')->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể xóa vĩnh viễn vì giá trị thuộc tính đang được sử dụng.',
            ], 409);
        }

        $attribute->forceDelete();

        return response()->json([
            'status' => 'success',
            'message' => 'Xóa vĩnh viễn thuộc tính thành công.',
        ]);
    }
}
