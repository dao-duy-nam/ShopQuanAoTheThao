<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\DanhGia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DanhGiaController extends Controller
{
    public function index(Request $request)
    {
        $query = DanhGia::with([
            'user:id,name',
            'product:id,ten',
            'variant.product:id,ten',
            'variant.variantAttributes.attributeValue.attribute',
        ])->where('is_hidden', 0);

        if ($request->search_user) {
            $query->whereHas('user', fn($q) =>
                $q->where('name', 'like', '%' . $request->search_user . '%')
            );
        }

        if ($request->search_product) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('product', fn($q2) =>
                    $q2->where('ten', 'like', '%' . $request->search_product . '%')
                )->orWhereHas('variant.product', fn($q3) =>
                    $q3->where('ten', 'like', '%' . $request->search_product . '%')
                );
            });
        }

        if ($request->search_rating) {
            $query->where('so_sao', $request->search_rating);
        }

        $reviews = $query->paginate(10);

        $data = $reviews->map(function ($review) {
            $base = [
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
                    'id' => $review->sanpham_id,
                    'name' => $review->product->ten ?? null,
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
            'product:id,ten',
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
                'id' => $review->sanpham_id,
                'name' => $review->product->ten ?? null,
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
