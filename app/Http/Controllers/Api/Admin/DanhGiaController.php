<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\DanhGia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DanhGiaController extends Controller
{
    public function filterDanhGia(Request $request)
{
    $query = DanhGia::with(['user', 'product', 'variant']);

    // Lọc theo số sao (ép kiểu int để đảm bảo đúng)
    if ($request->filled('so_sao')) {
        $query->where('so_sao', (int) $request->so_sao);
    }

    // Lọc theo nội dung (LIKE %...%)
    if ($request->filled('noi_dung')) {
        $query->where('noi_dung', 'like', '%' . $request->noi_dung . '%');
    }

    // Phân trang kết quả
    $danhGias = $query->orderBy('created_at', 'desc')->paginate(10);

    // Nếu không có đánh giá nào khớp
    if ($danhGias->total() === 0) {
        return response()->json([
            'status' => 'error',
            'message' => 'Không tìm thấy đánh giá.'
        ]);
    }

    // Trả về danh sách đánh giá
    return response()->json([
        'message' => 'Danh sách đánh giá đã lọc',
        'status' => 200,
        'data' => $danhGias->items(),
        'pagination' => [
            'total' => $danhGias->total(),
            'per_page' => $danhGias->perPage(),
            'current_page' => $danhGias->currentPage(),
            'last_page' => $danhGias->lastPage(),
        ]
    ]);
}



    public function index(Request $request)
    {
        $query = DanhGia::with([
            'user:id,name',
            'product:id,ten,hinh_anh',
            'variant.product:id,ten',
            'variant.variantAttributes.attributeValue.attribute',
        ]);



        $reviews = $query->orderBy('created_at', 'desc')->paginate(10);

        $data = $reviews->map(function ($review) {
            $base = [
                'id' => $review->id,
                'user' => [
                    'id' => $review->user->id,
                    'name' => $review->user->name,
                ],
                'content' => $review->noi_dung,
                'so_sao' => $review->so_sao,
                'image' => $review->hinh_anh,
                'created_at' => $review->created_at,
                'updated_at' => $review->updated_at,
                'is_hidden' => $review->is_hidden,
            ];

            if ($review->bien_the_id && $review->variant) {
                $base['variant'] = [
                    'id' => $review->variant->id,
                    'product_name' => $review->variant->product->ten ?? null,
                    'attributes' => $review->variant->variantAttributes->map(function ($va) {
                        return [
                            'attribute_name' => $va->attributeValue->attribute->ten ?? null,
                            'value' => $va->attributeValue->gia_tri ?? null,
                        ];
                    }),
                ];
            } else {
                $base['product'] = [
                    'id' => $review->san_pham_id,
                    'name' => $review->product->ten ?? null,
                    'image' => $review->product->hinh_anh ?? null,
                ];
            }

            return $base;
        });

        return response()->json([
            'status' => 'danh sách',
            'data' => $data,
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'total_pages' => $reviews->lastPage(),
                'total_items' => $reviews->total(),
                'per_page' => $reviews->perPage(),
            ]
        ]);
    }

    public function show($id)
    {
        $review = DanhGia::with([
            'user:id,name',
            'product:id,ten,hinh_anh',
            'variant.product:id,ten',
            'variant.variantAttributes.attributeValue.attribute',
        ])->find($id);

        if (!$review) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy đánh giá.'
            ], 404);
        }

        $data = [
            'id' => $review->id,
            'user' => [
                'id' => $review->user->id,
                'name' => $review->user->name,
            ],
            'content' => $review->noi_dung,
            'rating' => $review->so_sao,
            'image' => $review->hinh_anh,
            'created_at' => $review->created_at,
            'updated_at' => $review->updated_at,
        ];

        if ($review->bien_the_id && $review->variant) {
            $data['variant'] = [
                'id' => $review->variant->id,
                'product_name' => $review->variant->product->ten ?? null,
                'attributes' => $review->variant->variantAttributes->map(function ($va) {
                    return [
                        'attribute_name' => $va->attributeValue->attribute->ten ?? null,
                        'value' => $va->attributeValue->gia_tri ?? null,
                    ];
                }),
            ];
        } else {
            $data['product'] = [
                'id' => $review->san_pham_id,
                'name' => $review->product->ten ?? null,
                'image' => $review->product->hinh_anh ?? null,
            ];
        }

        return response()->json([
            'status' => 'chi tiết',
            'data' => $data
        ]);
    }

    public function toggleVisibility($id)
    {
        $review = DanhGia::find($id);

        if (!$review) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy đánh giá.'
            ], 404);
        }

        $review->is_hidden = !$review->is_hidden;
        $review->save();

        return response()->json([
            'status' => 'success',
            'message' => $review->is_hidden ? 'Đánh giá đã được ẩn.' : 'Đánh giá đã được hiển thị.',
            'data' => [
                'id' => $review->id,
                'is_hidden' => $review->is_hidden
            ]
        ]);
    }
}
