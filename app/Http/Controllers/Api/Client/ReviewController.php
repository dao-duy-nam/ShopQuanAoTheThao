<?php

namespace App\Http\Controllers\API\Client;

use App\Models\DanhGia;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ReviewResource;
use Illuminate\Support\Facades\Storage;


class ReviewController extends Controller
{
    public function index(Request $request, $id)
    {

        $query = DanhGia::with(['user', 'product:id,ten,hinh_anh', 'variant.product:id,ten,hinh_anh'])
            ->where('san_pham_id', $id)
            ->where('is_hidden', false);


        if ($request->filled('sao')) {
            $query->where('so_sao', $request->sao);
        } elseif ($request->boolean('co_hinh_anh')) {
            $query->whereNotNull('hinh_anh')->where('hinh_anh', '!=', '');
        }


        $danhGias = $query->latest()->paginate(10);



        $tongDanhGia = DanhGia::where('san_pham_id', $id)
            ->where('is_hidden', false)
            ->count();

        $tongSao = DanhGia::where('san_pham_id', $id)
            ->where('is_hidden', false)
            ->sum('so_sao');

        $trungBinhSao = $tongDanhGia > 0 ? round($tongSao / $tongDanhGia, 1) : 0;


        $thongKe = DanhGia::selectRaw('so_sao, COUNT(*) as so_luong')
            ->where('san_pham_id', $id)
            ->where('is_hidden', false)
            ->groupBy('so_sao')
            ->orderByDesc('so_sao')
            ->get()
            ->keyBy('so_sao');

        return response()->json([
            'data' => ReviewResource::collection($danhGias),
            'meta' => [
                'trung_binh_sao' => $trungBinhSao,
                'so_luong_theo_sao' => [
                    5 => $thongKe[5]->so_luong ?? 0,
                    4 => $thongKe[4]->so_luong ?? 0,
                    3 => $thongKe[3]->so_luong ?? 0,
                    2 => $thongKe[2]->so_luong ?? 0,
                    1 => $thongKe[1]->so_luong ?? 0,
                ],
                'tong_danh_gia' => $tongDanhGia,
            ],
            'status' => 200,
            'message' => 'Hiển thị danh sách đánh giá thành công',
        ]);
    }
    public function topFiveStarReviews(Request $request)
    {
        $limit = $request->input('limit', 10);

        $query = DanhGia::with(['user', 'product:id,ten,hinh_anh', 'variant.product:id,ten,hinh_anh'])
            ->where('so_sao', 5)
            ->where('is_hidden', false)
            ->orderByDesc('created_at');


        $danhGias = $query->limit($limit)->get();

        return response()->json([
            'data' => ReviewResource::collection($danhGias),
            'status' => 200,
            'message' => 'Hiển thị đánh giá 5 sao mới nhất thành công',
        ]);
    }


    public function store(Request $request)
    {
        $userId = Auth::id();


        $request->validate([
            'reviews' => 'required|array|min:1',
            'reviews.*.san_pham_id' => 'required|exists:san_phams,id',
            'reviews.*.bien_the_id' => 'required|exists:bien_thes,id',
            'reviews.*.noi_dung' => 'required|string',
            'reviews.*.so_sao' => 'required|integer|min:1|max:5',
            'reviews.*.hinh_anh' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'reviews.required' => 'Vui lòng gửi ít nhất một đánh giá.'
        ]);

        $createdReviews = [];
        $skippedReviews = [];

        foreach ($request->reviews as $index => $reviewData) {

            $orderDetail = OrderDetail::whereHas('order', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('trang_thai_don_hang', 'da_nhan');
            })
                ->where('san_pham_id', $reviewData['san_pham_id'])
                ->where('bien_the_id', $reviewData['bien_the_id'])
                ->whereDoesntHave('danhGias', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->latest()
                ->first();

            if (!$orderDetail) {
                $skippedReviews[] = [
                    'san_pham_id' => $reviewData['san_pham_id'],
                    'message' => 'Sản phẩm chưa mua hoặc đã đánh giá.'
                ];
                continue;
            }

            $imagePath = null;
            if ($request->hasFile("reviews.$index.hinh_anh")) {
                $file = $request->file("reviews.$index.hinh_anh");
                $imagePath = $file->store('reviews', 'public');
            }

            $createdReviews[] = DanhGia::create([
                'user_id' => $userId,
                'chi_tiet_don_hang_id' => $orderDetail->id,
                'san_pham_id' => $reviewData['san_pham_id'],
                'bien_the_id' => $reviewData['bien_the_id'],
                'noi_dung' => $reviewData['noi_dung'],
                'so_sao' => $reviewData['so_sao'],
                'hinh_anh' => $imagePath,
            ]);
        }

        $message = count($createdReviews) > 0
            ? 'Đánh giá đã được thêm thành công.'
            : 'Không có đánh giá nào được thêm.';

        return response()->json([
            'message' => $message,
            'created' => $createdReviews,
            'skipped' => $skippedReviews
        ], 201);
    }




    public function update(Request $request, $id)
    {
        $userId = Auth::id();

        $review = DanhGia::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$review) {
            return response()->json([
                'message' => 'Không tìm thấy đánh giá hoặc bạn không có quyền chỉnh sửa.'
            ], 404);
        }


        if (now()->diffInDays($review->created_at) > 7) {
            return response()->json([
                'message' => 'Bạn chỉ có thể chỉnh sửa đánh giá trong vòng 7 ngày.'
            ], 403);
        }

        $data = $request->validate([
            'noi_dung' => 'required|string',
            'so_sao'   => 'required|integer|min:1|max:5',
            'hinh_anh' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'noi_dung.required' => 'Vui lòng nhập nội dung đánh giá.',
            'so_sao.required'   => 'Vui lòng chọn số sao.',
        ]);

        if ($request->hasFile('hinh_anh')) {
            if ($review->hinh_anh) {
                Storage::disk('public')->delete($review->hinh_anh);
            }
            $data['hinh_anh'] = $request->file('hinh_anh')->store('reviews', 'public');
        } else {
            $data['hinh_anh'] = $review->hinh_anh;
        }

        $review->update($data);

        return response()->json([
            'message' => 'Cập nhật đánh giá thành công',
            'data'    => $review,
        ]);
    }

    public function destroy($id)
    {
        $userId = Auth::id();

        $review = DanhGia::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$review) {
            return response()->json([
                'message' => 'Không tìm thấy đánh giá hoặc bạn không có quyền xóa.'
            ], 404);
        }


        if (now()->diffInDays($review->created_at) > 7) {
            return response()->json([
                'message' => 'Bạn chỉ có thể xóa đánh giá trong vòng 7 ngày.'
            ], 403);
        }

        if ($review->hinh_anh) {
            Storage::disk('public')->delete($review->hinh_anh);
        }

        $review->delete();

        return response()->json([
            'message' => 'Xóa đánh giá thành công'
        ]);
    }
}
