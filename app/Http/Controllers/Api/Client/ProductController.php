<?php

namespace App\Http\Controllers\Api\Client;

use App\Models\Product;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $query = Product::with(['variants.attributeValues.attribute']);
        if ($request->filled('keyword')) {
            $keyword = strtolower($request->keyword);
            $query->whereRaw('LOWER(ten) LIKE ?', ["%{$keyword}%"]);
        }
        if ($request->filled('danh_muc_id')) {
            $query->where('danh_muc_id', $request->danh_muc_id);
        }
        if ($request->filled('gia_min') || $request->filled('gia_max')) {
            $query->whereHas('variants', function ($q) use ($request) {
                if ($request->filled('gia_min')) {
                    $giaMin = $request->gia_min;
                    $q->where(function ($sub) use ($giaMin) {
                        $sub->whereNotNull('gia_khuyen_mai')->where('gia_khuyen_mai', '>=', $giaMin)
                            ->orWhere(function ($q2) use ($giaMin) {
                                $q2->whereNull('gia_khuyen_mai')->where('gia', '>=', $giaMin);
                            });
                    });
                }

                if ($request->filled('gia_max')) {
                    $giaMax = $request->gia_max;
                    $q->where(function ($sub) use ($giaMax) {
                        $sub->whereNotNull('gia_khuyen_mai')->where('gia_khuyen_mai', '<=', $giaMax)
                            ->orWhere(function ($q2) use ($giaMax) {
                                $q2->whereNull('gia_khuyen_mai')->where('gia', '<=', $giaMax);
                            });
                    });
                }
            });
        }
        $query->withMin('variants', DB::raw('COALESCE(gia_khuyen_mai, gia)'));

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $sortableFields = ['variants_min_gia_ban_min', 'ten', 'created_at'];
        if (in_array($sortBy, $sortableFields)) {
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
