<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['variants.attributeValues.attribute'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 12));

        return ProductResource::collection($query);
    }

    public function show($id)
    {
        $product = Product::with(['variants.attributeValues.attribute'])->find($id);
        if (!$product) {
            return response()->json([
                'message' => 'Sản phẩm không tồn tại.',
            ], 404);
        }
        return new ProductResource($product);
    }

    public function filter(Request $request)
    {
        $query = Product::query()
            ->select('san_phams.*')
            ->join('bien_thes', 'san_phams.id', '=', 'bien_thes.san_pham_id')
            ->when(
                $request->filled('keyword'),
                fn($q) =>
                $q->where('san_phams.ten', 'like', '%' . $request->keyword . '%')
            )
            ->when(
                $request->filled('danh_muc_id'),
                fn($q) =>
                $q->where('san_phams.danh_muc_id', $request->danh_muc_id)
            )
            ->when(
                $request->filled('gia_min'),
                fn($q) =>
                $q->where('bien_thes.gia', '>=', $request->gia_min)
            )
            ->when(
                $request->filled('gia_max'),
                fn($q) =>
                $q->where('bien_thes.gia', '<=', $request->gia_max)
            )
            ->when(
                $request->filled('size'),
                fn($q) =>
                $q->where('bien_thes.size', $request->size)
            )
            ->groupBy('san_phams.id')
            ->selectRaw('MIN(bien_thes.gia) as min_gia');


        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        if (in_array($sortBy, ['min_gia', 'san_phams.ten', 'san_phams.created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $perPage = $request->get('per_page', 12);
        $products = $query->paginate($perPage);

        return ProductResource::collection($products);
    }

    public function related($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'message' => 'Sản phẩm không tồn tại.',
            ], 404);
        }

        $relatedProducts = Product::with(['variants.attributeValues.attribute'])
            ->where('danh_muc_id', $product->danh_muc_id)
            ->where('id', '!=', $product->id)
            ->latest()
            ->limit(4)
            ->get();

        return ProductResource::collection($relatedProducts);
    }
}
