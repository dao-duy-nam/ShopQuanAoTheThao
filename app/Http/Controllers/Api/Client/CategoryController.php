<?php

namespace App\Http\Controllers\Api\client;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

        if ($request->has('search') && $request->search != '') {
            $keyword = $request->search;
            $query->where('ten', 'like', '%' . $keyword . '%');
        }

        $categories = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'message' => 'Danh sách danh mục',
            'status' => 200,
            'data' => $categories
        ], 200);
    }
}
