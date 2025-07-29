<?php

namespace App\Http\Controllers\Api\Client;

use App\Models\Product;
use App\Models\Attribute;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;

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
                fn($q) => $q->whereRaw('LOWER(ten) LIKE ?', ['%' . strtolower($request->keyword) . '%'])
            )
            ->when(
                $request->filled('danh_muc_id'),
                fn($q) => $q->where('danh_muc_id', $request->danh_muc_id)
            )
            ->whereHas('variants', function ($q) use ($request) {
                if ($request->filled('gia_min')) {
                    $q->where('gia_khuyen_mai', '>=', $request->gia_min);
                }
                if ($request->filled('gia_max')) {
                    $q->where('gia_khuyen_mai', '<=', $request->gia_max);
                }
            })
            ->withMin('variants', 'gia_khuyen_mai');

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        if (in_array($sortBy, ['variants_min_gia_khuyen_mai', 'ten', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $perPage = $request->get('per_page', 12);
        $products = $query->paginate($perPage);
        if ($products->isEmpty()) {
            return response()->json([
                'message' => 'Không tìm thấy sản phẩm nào phù hợp.',
                'data' => [],
            ], 200);
        }

        return ProductResource::collection($products);
    }

    public function filterByAttributeValues(Request $request)
    {
        $attributeValueIds = $request->input('attribute_value_id', []);
        if (!is_array($attributeValueIds)) {
            $attributeValueIds = [$attributeValueIds];
        }
        $count = count($attributeValueIds);

        $query = Product::whereHas('variants', function ($q) use ($attributeValueIds, $count) {
            $q->whereHas('attributeValues', function ($q2) use ($attributeValueIds) {
                $q2->whereIn('gia_tri_thuoc_tinhs.id', $attributeValueIds);
            }, '=', $count);
        })->with([
            'variants' => function ($variantQuery) use ($attributeValueIds, $count) {
                $variantQuery->whereHas('attributeValues', function ($q2) use ($attributeValueIds) {
                    $q2->whereIn('gia_tri_thuoc_tinhs.id', $attributeValueIds);
                }, '=', $count);
            },
            'variants.attributeValues.attribute',
        ]);
        $products = $query->paginate(12);
        if ($products->isEmpty()) {
            return response()->json([
                'message' => 'Không tìm thấy sản phẩm phù hợp với thuộc tính đã chọn.',
                'data' => [],
            ], 404);
        }

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

    public function getFilterableValues()
    {
        $values = Attribute::with(['values' => function ($query) {
            $query->whereHas('variants')
                ->orderBy('id');
        }])
            ->whereHas('values.variants')
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $values,
        ]);
    }
}
