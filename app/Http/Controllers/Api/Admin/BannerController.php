<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Banner;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        return Banner::orderBy('created_at', 'desc')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tieu_de' => 'required|string|max:255',
            'hinh_anh' => 'required||image|mimes:jpeg,png,jpg,gif|max:2048',
            'link' => 'nullable|string',
            'trang_thai' => 'boolean',
        ]);
        if ($request->hasFile('hinh_anh')) {
            $validated['hinh_anh'] = $request->file('hinh_anh')->store('banner', 'public');
        }
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

        $validated = $request->validate([
            'tieu_de' => 'sometimes|required|string|max:255',
            'hinh_anh' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'link' => 'nullable|string',
            'trang_thai' => 'boolean',
        ]);

        if ($request->hasFile('hinh_anh')) {

            if ($banner->hinh_anh && Storage::disk('public')->exists($banner->hinh_anh)) {
                Storage::disk('public')->delete($banner->hinh_anh);
            }


            $validated['hinh_anh'] = $request->file('hinh_anh')->store('banner', 'public');
        }

        $banner->update($validated);

        return response()->json($banner);
    }

    // XÓA MỀM
    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->delete();
        return response()->json(['message' => 'Xóa mềm banner thành công']);
    }

    // LẤY DANH SÁCH ĐÃ XÓA
    public function trash()
    {
        return Banner::onlyTrashed()->get();
    }

    // KHÔI PHỤC
    public function restore($id)
    {
        $banner = Banner::onlyTrashed()->findOrFail($id);
        $banner->restore();
        return response()->json(['message' => 'Khôi phục banner thành công']);
    }

    // XÓA VĨNH VIỄN
    public function forceDelete($id)
    {
        $banner = Banner::onlyTrashed()->findOrFail($id);
        $banner->forceDelete();
        return response()->json(['message' => 'Xóa vĩnh viễn banner thành công']);
    }
}
