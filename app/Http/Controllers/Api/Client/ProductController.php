<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function index(Request $request)
    {
        $query = Product::with(['variants.color', 'variants.size']); 
        if ($request->filled('keyword')) {
            $query->where('ten', 'like', '%' . $request->keyword . '%');
        }
        if ($request->filled('mau_sac_id')) {
            $mauSacId = $request->mau_sac_id;
            $query->whereHas('variants', function ($q) use ($mauSacId) {
                $q->where('mau_sac_id', $mauSacId);
            });
        }
        if ($request->filled('kich_co_id')) {
            $kichCoId = $request->kich_co_id;
            $query->whereHas('variants', function ($q) use ($kichCoId) {
                $q->where('kich_co_id', $kichCoId);
            });
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
        return response()->json($products);
    }

    public function show($id)
    {
        $product = Product::with(['variants.color', 'variants.size'])->find($id);
        if (!$product) {
            return response()->json([
                'message' => 'Product not found',
            ], 404);
        }
        return response()->json($product);
    }
}
