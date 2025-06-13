<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Product;
use App\Models\Variant;
use Illuminate\Http\Request;
use App\Services\VariantService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Cloudinary\Api\Upload\UploadApi;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;

class ProductController extends Controller
{
    protected $variantService;

    public function __construct(VariantService $variantService)
    {
        $this->variantService = $variantService;
    }

    public function index(Request $request)
    {
        $query = Product::with(['variants.size', 'variants.color']);

        if ($request->has('keyword')) {
            $query->where('ten', 'like', '%' . $request->keyword . '%');
        }
        $products = $query->latest()->paginate(10);
        if ($products->isEmpty()) {
            return response()->json([
                'data' => [],
                'status' => 200,
                'message' => $request->filled('keyword')
                    ? 'Không tìm thấy sản phẩm nào với từ khóa "' . $request->keyword . '"'
                    : 'Không có sản phẩm nào trong trang này',
            ]);
        }
        return response()->json([
            'data' => ProductResource::collection($products),
            'status' => 200,
            'message' => 'Hiển thị danh sách sản phẩm thành công',
        ]);
    }

    public function store(StoreProductRequest  $request)
    {
        $data = $request->validated();
        $product = Product::create([
            'ten' => $data['ten'],
            'mo_ta' => $data['mo_ta'] ?? null,
            'hinh_anh' => $data['hinh_anh'] ?? null,
            'danh_muc_id' => $data['danh_muc_id'],
            'gia' => $data['gia'],
            'so_luong' => 0,
        ]);
        if (!empty($data['variants'])) {
            foreach ($data['variants'] as $i => &$variant) {
                $variant['hinh_anh'] = $request->file("variants.$i.hinh_anh") ?? null;
            }
            $this->variantService->createVariants($product, $data['variants']);
        }
        return response()->json([
            'data' => new ProductResource($product->fresh(['variants.size', 'variants.color'])),
            'status' => 201,
            'message' => 'Tạo sản phẩm thành công',
        ]);
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        return response()->json([
            'data' => new ProductResource($product->fresh(['variants.size', 'variants.color'])),
            'status' => 200,
            'message' => 'Hiển thị chi tiết sản phẩm thành công',
        ]);
    }

    public function update(UpdateProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $data = $request->validated();
        if (!empty($data['hinh_anh']) && !empty($data['hinh_anh_public_id'])) {
            if (!empty($product->hinh_anh_public_id)) {
                try {
                    $uploadApi = new UploadApi();
                    $uploadApi->destroy($product->hinh_anh_public_id);
                } catch (\Exception $e) {
                    Log::error("Xóa ảnh Cloudinary thất bại: " . $e->getMessage());
                }
            }
            $product->hinh_anh = $data['hinh_anh'];
            $product->hinh_anh_public_id = $data['hinh_anh_public_id'];
        }

        $product->update([
            'ten' => $data['ten'],
            'gia' => $data['gia'],
            'gia_khuyen_mai' => $data['gia_khuyen_mai'] ?? null,
            'so_luong' => $data['so_luong'],
            'mo_ta' => $data['mo_ta'] ?? null,
            'danh_muc_id' => $data['danh_muc_id'],
        ]);
        return response()->json([
            'data' => new ProductResource($product->fresh(['variants.Size', 'variants.Color'])),
            'status' => 200,
            'message' => 'Cập nhật sản phẩm thành công',
        ]);
    }



    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        Variant::where('san_pham_id', $product->id)->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Xóa sản phẩm thành công ',
        ]);
    }

    public function trashed()
    {
        $trashed = Product::onlyTrashed()->latest()->paginate(10);

        return response()->json([
            'data' => ProductResource::collection($trashed),
            'status' => 200,
            'message' => 'Hiển thị danh sách sản phẩm đã xóa',
        ]);
    }

    public function restore($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->restore();
        Variant::onlyTrashed()->where('san_pham_id', $product->id)->restore();

        return response()->json([
            'status' => 200,
            'message' => 'Khôi phục sản phẩm thành công',
        ]);
    }

    public function forceDelete($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);

        if ($product->hinh_anh) {
            Storage::disk('public')->delete($product->hinh_anh);
        }
        Variant::onlyTrashed()->where('san_pham_id', $product->id)->forceDelete();
        $product->forceDelete();

        return response()->json([
            'status' => 200,
            'message' => 'Đã xóa vĩnh viễn sản phẩm',
        ]);
    }
}
