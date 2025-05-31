<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DanhGiaController extends Controller
{
    /**
     * Xem danh sách đánh giá
     */
    public function index(Request $request)
    {
        $query = DB::table('danh_gias')
            ->join('users', 'danh_gias.user_id', '=', 'users.id')
            ->join('san_phams', 'danh_gias.san_pham_id', '=', 'san_phams.id')
            ->where('danh_gias.is_hidden', '=', 0) 
            ->select(
                'danh_gias.id',
                'danh_gias.user_id',
                'users.name as user_name',
                'danh_gias.san_pham_id',
                'san_phams.ten as product_name',
                'danh_gias.noi_dung',
                'danh_gias.so_sao',
                'danh_gias.hinh_anh',

                'danh_gias.created_at',
                'danh_gias.updated_at'
            );

        // Tìm kiếm theo tên người dùng
        if ($request->has('search_user') && $request->search_user) {
            $query->where('users.name', 'like', '%' . $request->search_user . '%');
        }

        // Tìm kiếm theo tên sản phẩm
        if ($request->has('search_product') && $request->search_product) {
            $query->where('san_phams.ten', 'like', '%' . $request->search_product . '%');
        }

        // Lọc theo số sao
        if ($request->has('search_rating') && $request->search_rating) {
            $query->where('danh_gias.so_sao', $request->search_rating);
        }

        $reviews = $query->paginate(10);

        // Định dạng dữ liệu thủ công
        $data = $reviews->items();
        $formattedData = array_map(function ($review) {
            return [
                'id' => $review->id,
                'user' => [
                    'id' => $review->user_id,
                    'name' => $review->user_name,
                ],
                'product' => [
                    'id' => $review->san_pham_id,
                    'name' => $review->product_name,
                ],
                'content' => $review->noi_dung,
                'rating' => $review->so_sao,
                'image' => $review->hinh_anh,

                'created_at' => $review->created_at,
                'updated_at' => $review->updated_at,
            ];
        }, $data);

        return response()->json([
            'status' => 'danh sách',
            'data' => $formattedData,
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'total_pages' => $reviews->lastPage(),
                'total_items' => $reviews->total(),
                'per_page' => $reviews->perPage(),
            ]
        ], 200);
    }

    /**
     * Xem chi tiết đánh giá
     */
    public function show($id)
    {
        $review = DB::table('danh_gias')
            ->join('users', 'danh_gias.user_id', '=', 'users.id')
            ->join('san_phams', 'danh_gias.san_pham_id', '=', 'san_phams.id')
            ->select(
                'danh_gias.id',
                'danh_gias.user_id',
                'users.name as user_name',
                'danh_gias.san_pham_id',
                'san_phams.ten as product_name',
                'danh_gias.noi_dung',
                'danh_gias.so_sao',
                'danh_gias.hinh_anh',

                'danh_gias.created_at',
                'danh_gias.updated_at'
            )
            ->where('danh_gias.id', $id)
            ->first();

        if (!$review) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy đánh giá.'
            ], 404);
        }

        return response()->json([
            'status' => 'chi tiết',
            'data' => [
                'id' => $review->id,
                'user' => [
                    'id' => $review->user_id,
                    'name' => $review->user_name,
                ],
                'product' => [
                    'id' => $review->san_pham_id,
                    'name' => $review->product_name,
                ],
                'content' => $review->noi_dung,
                'rating' => $review->so_sao,
                'image' => $review->hinh_anh,

                'created_at' => $review->created_at,
                'updated_at' => $review->updated_at,
            ]
        ], 200);
    }

    //  * ẩn đánh giá
    
   public function toggleVisibility($id)
{
    // Lấy thông tin đánh giá
    $review = DB::table('danh_gias')->where('id', $id)->first();

    if (!$review) {
        return response()->json([
            'status' => 'error',
            'message' => 'Không tìm thấy đánh giá.'
        ], 404);
    }

    // Đảo ngược trạng thái hiện/ẩn
    $newStatus = $review->is_hidden ? 0 : 1;

    // Cập nhật lại trạng thái
    DB::table('danh_gias')
        ->where('id', $id)
        ->update([
            'is_hidden' => $newStatus,
            'updated_at' => now()
        ]);

    return response()->json([
        'status' => 'success',
        'message' => $newStatus ? 'Đánh giá đã được ẩn.' : 'Đánh giá đã được hiển thị.',
        'data' => [
            'id' => $id,
            'is_hidden' => $newStatus
        ]
    ]);
}


   
}
