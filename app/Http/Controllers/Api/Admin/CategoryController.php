<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::orderBy('created_at', 'desc')->paginate(10);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ten' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
        ], [
            'ten.required' => 'Tên danh mục không được bỏ trống',
            'ten.string' => 'Tên danh mục phải là kiểu chuỗi văn bản',
            'ten.max' => 'Tên danh mục không được vượt quá 255 ký tự',
            'mo_ta.string' => 'Mô tả phải là kiểu chuỗi văn bản',
        ]);

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
        ], [
            'ten.required' => 'Tên danh mục không được bỏ trống',
            'ten.string' => 'Tên danh mục phải là kiểu chuỗi văn bản',
            'ten.max' => 'Tên danh mục không được vượt quá 255 ký tự',
            'mo_ta.string' => 'Mô tả phải là kiểu chuỗi văn bản',
        ]);

        $danhMuc->update($validated);
        return response()->json($danhMuc);
    }

    public function destroy($id)
    {
        $danhMuc = Category::findOrFail($id);
        $danhMuc->delete();
        return response()->json(['message' => 'Đã xóa mềm'], 200);
    }

    public function trash()
    {
        return Category::onlyTrashed()->get();
    }

    public function restore($id)
    {
        $danhMuc = Category::onlyTrashed()->findOrFail($id);
        $danhMuc->restore();
        return response()->json(['message' => 'Khôi phục thành công']);
    }

    public function forceDelete($id)
    {
        $danhMuc = Category::onlyTrashed()->findOrFail($id);
        $danhMuc->forceDelete();
        return response()->json(['message' => 'Xóa vĩnh viễn thành công']);
    }
}
