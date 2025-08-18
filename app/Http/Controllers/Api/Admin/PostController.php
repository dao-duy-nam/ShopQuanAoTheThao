<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->paginate(10);
        return response()->json($posts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tieu_de' => 'required|string|max:255',
            'mo_ta_ngan' => 'nullable|string',
            'noi_dung' => 'nullable|string',
            'anh_dai_dien' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'trang_thai' => 'nullable|in:an,hien',
        ]);

        $data = $request->only(['tieu_de', 'mo_ta_ngan', 'noi_dung', 'trang_thai']);

        if ($request->hasFile('anh_dai_dien')) {
            $path = $request->file('anh_dai_dien')->store('posts', 'public');
            $data['anh_dai_dien'] = $path;
        }

        $post = Post::create($data);

        return response()->json([
            'message' => 'Thêm bài viết thành công.',
            'data' => $post
        ]);
    }

    public function show($id)
    {
        $post = Post::findOrFail($id);
        $post->anh_dai_dien_url = $post->anh_dai_dien ? Storage::url($post->anh_dai_dien) : null;

        return response()->json($post);
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);


        $validatedData = $request->validate([
            'tieu_de' => 'sometimes|required|string|max:255',
            'mo_ta_ngan' => 'sometimes|nullable|string',
            'noi_dung' => 'sometimes|nullable|string',
            'anh_dai_dien' => 'sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'trang_thai' => 'sometimes|required|in:an,hien',
        ]);

        if ($request->hasFile('anh_dai_dien')) {
            if ($post->anh_dai_dien && Storage::disk('public')->exists($post->anh_dai_dien)) {
                Storage::disk('public')->delete($post->anh_dai_dien);
            }

            $path = $request->file('anh_dai_dien')->store('posts', 'public');
            $validatedData['anh_dai_dien'] = $path;
        }


        $post->update($validatedData);

        return response()->json([
            'message' => 'Cập nhật bài viết thành công.',
            'data' => $post->fresh()
        ]);
    }


    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return response()->json([
            'message' => 'Đã xóa bài viết thành công.',
        ]);
    }

    public function trash()
    {
        $posts = Post::onlyTrashed()->latest()->get();
        return response()->json($posts);
    }

    public function restore($id)
    {
        $post = Post::onlyTrashed()->findOrFail($id);
        $post->restore();

        return response()->json([
            'message' => 'Khôi phục bài viết thành công',
            'data' => $post
        ]);
    }
}
