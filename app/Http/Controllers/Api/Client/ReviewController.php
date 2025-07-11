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
    

    public function store(Request $request)
    {
        $dataValidate = $request->validate([
            'san_pham_id' => 'required|exists:san_phams,id',
            'bien_the_id' => 'nullable|exists:bien_thes,id',
            'noi_dung'    => 'required|string',
            'so_sao'      => 'required|integer|min:1|max:5',
            'hinh_anh.*'  => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
        ], [
            'san_pham_id.required' => 'Vui lòng chọn sản phẩm để đánh giá.',
            'san_pham_id.exists'   => 'Sản phẩm không tồn tại trong hệ thống.',

            'bien_the_id.exists'   => 'Biến thể không hợp lệ.',

            'noi_dung.required'    => 'Vui lòng nhập nội dung đánh giá.',
            'noi_dung.string'      => 'Nội dung đánh giá phải là văn bản.',

            'so_sao.required'      => 'Vui lòng chọn số sao.',
            'so_sao.integer'       => 'Số sao phải là một số nguyên.',
            'so_sao.min'           => 'Số sao tối thiểu là 1.',
            'so_sao.max'           => 'Số sao tối đa là 5.',

            'hinh_anh.*.image'     => 'Tệp tải lên phải là hình ảnh.',
            'hinh_anh.*.mimes'     => 'Ảnh phải có định dạng jpeg, png, jpg, gif hoặc svg.',
        ]);

        $userId = Auth::id();


        $hasPurchased = OrderDetail::whereHas('order', function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->where('trang_thai_don_hang', 'da_giao');
        })
            ->where('san_pham_id', $request->san_pham_id)
            ->when($request->bien_the_id, function ($query) use ($request) {
                $query->where('bien_the_id', $request->bien_the_id);
            })
            ->exists();

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


        $imagePaths = [];

        if ($request->hasFile('hinh_anh')) {
            $files = is_array($request->file('hinh_anh'))
                ? $request->file('hinh_anh')
                : [$request->file('hinh_anh')];

            foreach ($files as $image) {
                $path = $image->store('reviews', 'public');
                $imagePaths[] = $path;
            }
        }



        $review = DanhGia::create([
            'user_id'     => $userId,
            'san_pham_id' => $dataValidate['san_pham_id'],
            'bien_the_id' => $dataValidate['bien_the_id'] ?? null,
            'noi_dung'    => $dataValidate['noi_dung'],
            'so_sao'      => $dataValidate['so_sao'],
            'hinh_anh'    => $imagePaths,
        ]);

        return response()->json([
            'message' => 'Đánh giá đã được gửi thành công!',
            'data'    => $review
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'noi_dung'    => 'required|string',
            'so_sao'      => 'required|integer|min:1|max:5',
            'hinh_anh.*'  => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
        ], [
            'noi_dung.required'    => 'Vui lòng nhập nội dung đánh giá.',
            'noi_dung.string'      => 'Nội dung đánh giá phải là văn bản.',
            'so_sao.required'      => 'Vui lòng chọn số sao.',
            'so_sao.integer'       => 'Số sao phải là một số nguyên.',
            'so_sao.min'           => 'Số sao tối thiểu là 1.',
            'so_sao.max'           => 'Số sao tối đa là 5.',
            'hinh_anh.*.image'     => 'Tệp tải lên phải là hình ảnh.',
            'hinh_anh.*.mimes'     => 'Ảnh phải có định dạng jpeg, png, jpg, gif hoặc svg.',

        ]);

        $userId = Auth::id();

        $review = DanhGia::where('id', $id)->where('user_id', $userId)->first();

        if (!$review) {
            return response()->json([
                'message' => 'Không tìm thấy đánh giá hoặc bạn không có quyền chỉnh sửa.'
            ], 404);
        }

        $hasPurchased = OrderDetail::whereHas('order', function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->where('trang_thai_don_hang', 'da_giao');
        })
            ->where('san_pham_id', $review->san_pham_id)
            ->when($review->bien_the_id, function ($query) use ($review) {
                $query->where('bien_the_id', $review->bien_the_id);
            })
            ->exists();

        if (!$hasPurchased) {
            return response()->json([
                'message' => 'Bạn chỉ có thể chỉnh sửa đánh giá sau khi đã mua và nhận hàng.'
            ], 403);
        }



        if ($request->hasFile('hinh_anh')) {
            if ($review->hinh_anh) {
                foreach ($review->hinh_anh as $oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }
            }

            $imagePaths = [];
            foreach ($request->file('hinh_anh') as $file) {
                $imagePaths[] = $file->store('reviews', 'public');
            }
        } else {

            $imagePaths = $review->hinh_anh ?? [];
        }


        $review->update([
            'noi_dung'  => $data['noi_dung'],
            'so_sao'    => $data['so_sao'],
            'hinh_anh'  => $imagePaths,
        ]);

        return response()->json([
            'message' => 'Cập nhật đánh giá thành công!',
            'data' => $review
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


        if ($review->hinh_anh && is_array($review->hinh_anh)) {
            foreach ($review->hinh_anh as $imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
        }

        $review->delete();

        return response()->json(['message' => 'Xóa đánh giá thành công']);
    }
}
