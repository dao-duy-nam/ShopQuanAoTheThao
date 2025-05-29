<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
// use Illuminate\Support\Facades\Request;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        return Banner::orderBy('thu_tu')->get();
    }

    public function store(Request $request)
    {
    $validated = $request->validate([
        'tieu_de' => 'required|string|max:255',
        'hinh_anh' => 'required|string',
        'link' => 'nullable|string',
        'trang_thai' => 'boolean',
        'thu_tu' => 'integer'
    ]);

    $banner = Banner::create($validated);

    return response()->json($banner, 201);
    }

    public function show($id)
    {
        return Banner::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);
        $banner->update($request->all());
        return $banner;
    }

    //  XÓA MỀM
    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->delete();
        return response()->json(['message' => 'Xóa mềm banner thành công']);
    }

    //  LẤY DANH SÁCH ĐÃ XÓA
    public function trash()
    {
        return Banner::onlyTrashed()->orderBy('thu_tu')->get();
    }

    //  KHÔI PHỤC
    public function restore($id)
    {
        $banner = Banner::onlyTrashed()->findOrFail($id);
        $banner->restore();
        return response()->json(['message' => 'Khôi phục banner thành công']);
    }

    // 💣 XÓA VĨNH VIỄN
    public function forceDelete($id)
    {
        $banner = Banner::onlyTrashed()->findOrFail($id);
        $banner->forceDelete();
        return response()->json(['message' => 'Xóa vĩnh viễn banner thành công']);
    }
}


