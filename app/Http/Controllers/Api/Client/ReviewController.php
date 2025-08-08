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

        $query = DanhGia::with('user')
            ->where('san_pham_id', $id)
            ->where('is_hidden', false);


        if ($request->filled('sao')) {
            $query->where('so_sao', $request->sao);
        } elseif ($request->boolean('co_hinh_anh')) {
            $query->whereNotNull('hinh_anh')->where('hinh_anh', '!=', '');
        }


        $danhGias = $query->orderByDesc('so_sao')->paginate(10);


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

        $query = DanhGia::with('user')
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
        $dataValidate = $request->validate([
            'san_pham_id' => 'required|exists:san_phams,id',
            'bien_the_id' => 'required|exists:bien_thes,id',
            'noi_dung'    => 'required|string',
            'so_sao'      => 'required|integer|min:1|max:5',
            'hinh_anh'    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
        ], [
            'san_pham_id.required' => 'Vui lòng chọn sản phẩm để đánh giá.',
            'san_pham_id.exists'   => 'Sản phẩm không tồn tại trong hệ thống.',

            'bien_the_id.required' => 'Vui lòng chọn biến thể để đánh giá.',
            'bien_the_id.exists'   => 'Biến thể không hợp lệ.',

            'noi_dung.required'    => 'Vui lòng nhập nội dung đánh giá.',
            'noi_dung.string'      => 'Nội dung đánh giá phải là văn bản.',

            'so_sao.required'      => 'Vui lòng chọn số sao.',
            'so_sao.integer'       => 'Số sao phải là một số nguyên.',
            'so_sao.min'           => 'Số sao tối thiểu là 1.',
            'so_sao.max'           => 'Số sao tối đa là 5.',

            'hinh_anh.image'       => 'Tệp tải lên phải là hình ảnh.',
            'hinh_anh.mimes'       => 'Ảnh phải có định dạng jpeg, png, jpg, gif hoặc svg.',
        ]);

        $userId = Auth::id();

        $hasPurchased = OrderDetail::whereHas('order', function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->where('trang_thai_don_hang', 'da_nhan')
                ->where('updated_at', '>=', now()->subDays(7));
        })
            ->where('san_pham_id', $request->san_pham_id)
            ->when($request->bien_the_id, function ($query) use ($request) {
                $query->where('bien_the_id', $request->bien_the_id);
            })
            ->latest()
            ->first();

        if (!$hasPurchased) {
            return response()->json([
                'message' => 'Bạn chỉ có thể đánh giá sản phẩm sau khi đã mua và nhận hàng.'
            ], 403);
        }

        $alreadyReviewed = DanhGia::where('user_id', $userId)
            ->where('san_pham_id', $request->san_pham_id)
            ->when($request->bien_the_id, function ($query) use ($request) {
                $query->where('bien_the_id', $request->bien_the_id);
            })
            ->exists();

        if ($alreadyReviewed) {
            return response()->json([
                'message' => 'Bạn đã đánh giá sản phẩm này rồi.'
            ], 409);
        }

        $imagePath = null;
        if ($request->hasFile('hinh_anh')) {
            $imagePath = $request->file('hinh_anh')->store('reviews', 'public');
        }

        $review = DanhGia::create([
            'user_id'     => $userId,
            'san_pham_id' => $dataValidate['san_pham_id'],
            'bien_the_id' => $dataValidate['bien_the_id'],
            'noi_dung'    => $dataValidate['noi_dung'],
            'so_sao'      => $dataValidate['so_sao'],
            'hinh_anh'    => $imagePath,
        ]);

        return response()->json([
            'message' => 'Đánh giá đã được gửi thành công!',
            'data'    => $review
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $review = DanhGia::findOrFail($id);

        $data = $request->validate([
            'noi_dung'    => 'required|string',
            'so_sao'      => 'required|integer|min:1|max:5',
            'hinh_anh'    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'noi_dung.required'    => 'Vui lòng nhập nội dung đánh giá.',
            'noi_dung.string'      => 'Nội dung phải là văn bản.',
            'so_sao.required'      => 'Vui lòng chọn số sao.',
            'so_sao.integer'       => 'Số sao phải là một số nguyên.',
            'so_sao.min'           => 'Số sao tối thiểu là 1.',
            'so_sao.max'           => 'Số sao tối đa là 5.',
            'hinh_anh.image'       => 'Tệp phải là hình ảnh.',
            'hinh_anh.mimes'       => 'Ảnh phải thuộc định dạng jpeg, png, jpg, gif, svg.',
        ]);


        if ($request->hasFile('hinh_anh')) {

            if ($review->hinh_anh) {
                Storage::disk('public')->delete($review->hinh_anh);
            }

            $imagePath = $request->file('hinh_anh')->store('reviews', 'public');
        } else {
            $imagePath = $review->hinh_anh;
        }

        $review->update([
            'noi_dung'  => $data['noi_dung'],
            'so_sao'    => $data['so_sao'],
            'hinh_anh'  => $imagePath,
        ]);

        return response()->json([
            'message' => 'Cập nhật đánh giá thành công',
            'data'    => $review,
        ]);
    }



    public function destroy($id)
    {
        $userId = Auth::id();

        $review = DanhGia::where('id', $id)->where('user_id', $userId)->first();

        if (!$review) {
            return response()->json([
                'message' => 'Không tìm thấy đánh giá hoặc bạn không có quyền xóa đánh giá này.'
            ], 404);
        }


        if (!empty($review->hinh_anh)) {
            Storage::disk('public')->delete($review->hinh_anh);
        }

        $review->delete();

        return response()->json(['message' => 'Xóa đánh giá thành công']);
    }
}
