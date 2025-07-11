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
        $query = Product::with(['variants.attributeValues.attribute']);

        if ($request->filled('keyword')) {
            $query->where('ten', 'like', '%' . $request->keyword . '%');
        }

        if ($request->has('attribute_filter') && is_array($request->attribute_filter)) {
            foreach ($request->attribute_filter as $attrName => $attrValue) {
                $query->whereHas('variants.attributeValues', function ($q) use ($attrName, $attrValue) {
                    $q->whereRaw('LOWER(gia_tri) = ?', [mb_strtolower($attrValue)])
                        ->whereHas('attribute', function ($q2) use ($attrName) {
                            $q2->whereRaw('LOWER(ten) = ?', [mb_strtolower($attrName)]);
                        });
                });
            }
        }
        if ($request->filled('gia_min')) {
            $query->where('gia', '>=', $request->gia_min);
        }
        if ($request->filled('gia_max')) {
            $query->where('gia', '<=', $request->gia_max);
        }
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        if (in_array($sortBy, ['gia', 'ten', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        }
        $perPage = $request->get('per_page', 10);
        $products = $query->paginate($perPage);
        return ProductResource::collection($products);
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
}
