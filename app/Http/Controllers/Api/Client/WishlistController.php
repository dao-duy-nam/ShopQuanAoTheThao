<?php

namespace App\Http\Controllers\Api\Client;

use App\Models\Wishlist;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ProductResource;
use App\Http\Resources\WishlistResource;

class WishlistController extends Controller
{

    public function index(Request $request)
    {
        $userId = Auth::id();

        $wishlists = Wishlist::where('nguoi_dung_id', $userId)
            ->with([
                'product.category',
                'product.variants.attributeValues.attribute'
            ])
            ->paginate($request->get('per_page', 12));

        return WishlistResource::collection($wishlists)
            ->additional([
                'status'  => true,
                'message' => 'Danh sách sản phẩm yêu thích',
            ]);
    }


    public function store(Request $request)
    {

        $request->validate([
            'san_pham_id' => 'required|exists:san_phams,id',
        ]);

        $userId = Auth::id();
        $productId = $request->san_pham_id;


        $exists = Wishlist::where('nguoi_dung_id', $userId)
            ->where('san_pham_id', $productId)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'Sản phẩm đã có trong danh sách yêu thích.',
            ], 409);
        }


        $wishlist = Wishlist::create([
            'nguoi_dung_id' => $userId,
            'san_pham_id' => $productId,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Đã thêm sản phẩm vào danh sách yêu thích.',
            'data' => $wishlist,
        ]);
    }


    public function destroy($id)
    {
        $userId = Auth::id();


        $deleted = Wishlist::where('nguoi_dung_id', $userId)
            ->where('san_pham_id', $id)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy sản phẩm trong danh sách yêu thích hoặc đã bị xóa.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Đã xóa sản phẩm khỏi danh sách yêu thích.',
        ]);
    }
}
