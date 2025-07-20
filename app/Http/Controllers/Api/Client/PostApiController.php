<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostApiController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 6); 

        $posts = Post::where('trang_thai', 'hien')
            ->latest()
            ->select(['id', 'tieu_de', 'mo_ta_ngan', 'anh_dai_dien', 'created_at'])
            ->paginate($perPage);

        return response()->json([
            'message' => 'Danh sách bài viết',
            'status' => 200,
            'data' => $posts
        ], 200);
    }

    public function show($id)
    {
        $post = Post::where('trang_thai', 'hien')->find($id);

        if (!$post) {
            return response()->json(['message' => 'Bài viết không tồn tại hoặc đã bị ẩn'], 404);
        }

        return response()->json([
            'message' => 'Chi tiết bài viết',
            'status' => 200,
            'data' => $post
        ], 200);
    }
}
