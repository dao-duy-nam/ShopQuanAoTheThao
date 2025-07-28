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
        $query = Product::with(['variants.attributeValues.attribute'])
            ->when(
                $request->filled('keyword'),
                fn($q) => $q->where('ten', 'like', '%' . $request->keyword . '%')
            )
            ->when(
                $request->filled('danh_muc_id'),
                fn($q) => $q->where('danh_muc_id', $request->danh_muc_id)
            )
            ->whereHas('variants', function ($q) use ($request) {
                if ($request->filled('gia_min')) {
                    $q->where('gia', '>=', $request->gia_min);
                }
                if ($request->filled('gia_max')) {
                    $q->where('gia', '<=', $request->gia_max);
                }
                if ($request->filled('size')) {
                    $q->where('size', $request->size);
                }
            })
            ->withMin('variants', 'gia');

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['variants_min_gia', 'ten', 'created_at'])) {
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
