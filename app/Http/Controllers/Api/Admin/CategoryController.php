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

        return $query->orderBy('created_at', 'desc')->paginate(10);
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
        return response()->json($danhMuc, 201);
    }

    public function show($id)
    {
        $danhMuc = Category::findOrFail($id);
        return response()->json($danhMuc);
    }

    public function update(Request $request, $id)
    {
        $danhMuc = Category::findOrFail($id);

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
            // Xóa ảnh cũ nếu có
            if ($danhMuc->image && Storage::disk('public')->exists($danhMuc->image)) {
                Storage::disk('public')->delete($danhMuc->image);
            }

            $validated['image'] = $request->file('image')->store('category', 'public');
        }

        $danhMuc->update($validated);
        return response()->json($danhMuc);
    }

    public function destroy($id)
    {
        $danhMuc = Category::findOrFail($id);

        
        if ($danhMuc->ten === 'Không phân loại') {
            return response()->json(['error' => 'Danh mục "Không phân loại" không được phép xóa'], 403);
        }

        
        $defaultCategory = Category::where('ten', 'Không phân loại')->first();
        if (!$defaultCategory) {
            return response()->json(['error' => 'Không tìm thấy danh mục mặc định'], 404);
        }

        
        Product::where('danh_muc_id', $danhMuc->id)->update([
            'danh_muc_id' => $defaultCategory->id
        ]);

        $danhMuc->delete();
        return response()->json(['message' => 'Đã xóa mềm danh mục và chuyển sản phẩm sang "Không phân loại"']);
    }


    public function trash()
    {
        return Category::onlyTrashed()->get();
    }

    public function restore($id)
    {
        $danhMuc = Category::onlyTrashed()->findOrFail($id);
        $danhMuc->restore();
        return response()->json(['message' => 'Khôi phục danh mục thành công']);
    }

    public function forceDelete($id)
    {
        $danhMuc = Category::onlyTrashed()->findOrFail($id);

        
        if ($danhMuc->ten === 'Không phân loại') {
            return response()->json(['error' => 'Danh mục này không được phép xóa vĩnh viễn'], 403);
        }

        
        if ($danhMuc->image && Storage::disk('public')->exists($danhMuc->image)) {
            Storage::disk('public')->delete($danhMuc->image);
        }

        $danhMuc->forceDelete();
        return response()->json(['message' => 'Xóa vĩnh viễn danh mục thành công']);
    }
}
