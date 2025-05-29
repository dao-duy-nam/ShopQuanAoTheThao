<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

        if ($request->has('search') && $request->search != '') {
            $keyword = $request->search;
            $query->where('ten', 'like', '%' . $keyword . '%');
        }

        $categories = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'message' => 'Danh sách danh mục',
            'status' => 200,
            'data' => $categories
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ten' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'ten.required' => 'Tên danh mục không được bỏ trống',
            'ten.string' => 'Tên danh mục phải là chuỗi',
            'ten.max' => 'Tên danh mục không được vượt quá 255 ký tự',
            'mo_ta.string' => 'Mô tả phải là chuỗi',
            'image.image' => 'Tệp tải lên phải là hình ảnh',
            'image.mimes' => 'Ảnh phải có định dạng jpeg, png, jpg, gif',
            'image.max' => 'Ảnh không được vượt quá 2MB',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('category', 'public');
        }

        $danhMuc = Category::create($validated);

        return response()->json([
            'message' => 'Tạo danh mục thành công',
            'status' => 201,
            'data' => $danhMuc
        ], 201);
    }

    public function show($id)
    {
        $danhMuc = Category::findOrFail($id);

        return response()->json([
            'message' => 'Chi tiết danh mục',
            'status' => 200,
            'data' => $danhMuc
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $danhMuc = Category::findOrFail($id);

        if ($danhMuc->ten === 'Không phân loại') {
            return response()->json([
                'message' => 'Danh mục Không phân loại không được phép cập nhật',
                'status' => 403,
                'data' => null
            ], 403);
        }
        $validated = $request->validate([
            'ten' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'ten.required' => 'Tên danh mục không được bỏ trống',
            'ten.string' => 'Tên danh mục phải là chuỗi',
            'ten.max' => 'Tên danh mục không được vượt quá 255 ký tự',
            'mo_ta.string' => 'Mô tả phải là chuỗi',
            'image.image' => 'Tệp tải lên phải là hình ảnh',
            'image.mimes' => 'Ảnh phải có định dạng jpeg, png, jpg, gif',
            'image.max' => 'Ảnh không được vượt quá 2MB',
        ]);

        if ($request->hasFile('image')) {
            if ($danhMuc->image && Storage::disk('public')->exists($danhMuc->image)) {
                Storage::disk('public')->delete($danhMuc->image);
            }

            $validated['image'] = $request->file('image')->store('category', 'public');
        }

        $danhMuc->update($validated);

        return response()->json([
            'message' => 'Cập nhật danh mục thành công',
            'status' => 200,
            'data' => $danhMuc
        ], 200);
    }

    public function destroy($id)
    {
        $danhMuc = Category::findOrFail($id);

        if ($danhMuc->ten === 'Không phân loại') {
            return response()->json([
                'message' => 'Danh mục Không phân loại không được phép xóa',
                'status' => 403,
                'data' => null
            ], 403);
        }

        $defaultCategory = Category::where('ten', 'Không phân loại')->first();
        if (!$defaultCategory) {
            return response()->json([
                'message' => 'Không tìm thấy danh mục mặc định',
                'status' => 404,
                'data' => null
            ], 404);
        }

        Product::where('danh_muc_id', $danhMuc->id)->update([
            'id_danh_muc_cu' => $danhMuc->id,
            'danh_muc_id' => $defaultCategory->id
        ]);

        $danhMuc->delete();

        return response()->json([
            'message' => 'Đã xóa mềm danh mục và chuyển sản phẩm sang không phân loại',
            'status' => 200,
            'data' => null
        ], 200);
    }

    public function trash()
    {
        $trashed = Category::onlyTrashed()->get();

        return response()->json([
            'message' => 'Danh sách danh mục đã xóa mềm',
            'status' => 200,
            'data' => $trashed
        ], 200);
    }

    public function restore($id)
    {
        $danhMuc = Category::onlyTrashed()->findOrFail($id);
        $danhMuc->restore();

        Product::where('id_danh_muc_cu', $id)->update([
            'danh_muc_id' => $id,
            'id_danh_muc_cu' => null
        ]);

        return response()->json([
            'message' => 'Khôi phục danh mục và các sản phẩm liên quan thành công',
            'status' => 200,
            'data' => $danhMuc
        ], 200);
    }

    public function forceDelete($id)
    {
        $danhMuc = Category::onlyTrashed()->findOrFail($id);

        if ($danhMuc->ten === 'Không phân loại') {
            return response()->json([
                'message' => 'Danh mục này không được phép xóa vĩnh viễn',
                'status' => 403,
                'data' => null
            ], 403);
        }

        if ($danhMuc->image && Storage::disk('public')->exists($danhMuc->image)) {
            Storage::disk('public')->delete($danhMuc->image);
        }

        $danhMuc->forceDelete();

        return response()->json([
            'message' => 'Xóa vĩnh viễn danh mục thành công',
            'status' => 200,
            'data' => null
        ], 200);
    }
}
