<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Size;
use App\Models\Color;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Http\Request;
use App\Services\VariantService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
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
        $query = Product::query();

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

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();


        $imagePaths = [];
        if ($request->hasFile('hinh_anh')) {
            foreach ($request->file('hinh_anh') as $image) {
                $imagePaths[] = $image->store('products', 'public');
            }
        }


        if (empty($data['variants'])) {

            Size::firstOrCreate(['kich_co'      => 'DEFAULT']);
            Color::firstOrCreate(['ten_mau_sac' => 'DEFAULT']);


            $data['variants'] = [[
                'kich_co'        => 'DEFAULT',
                'mau_sac'        => 'DEFAULT',
                'so_luong'       => $data['so_luong'],
                'gia'            => $data['gia'],
                'gia_khuyen_mai' => $data['gia_khuyen_mai'] ?? null,
                'hinh_anh'       => null,
            ]];
        }


        foreach ($data['variants'] as $i => &$variant) {
            $variant['hinh_anh'] = $request->hasFile("variants.$i.hinh_anh")
                ? $request->file("variants.$i.hinh_anh")->store('variant-images', 'public')
                : ($variant['hinh_anh'] ?? null);
        }


        $totalQty = collect($data['variants'])->sum('so_luong');

        $product = Product::create([
            'ten'            => $data['ten'],
            'mo_ta'          => $data['mo_ta'] ?? null,
            'hinh_anh'       => json_encode($imagePaths),
            'danh_muc_id'    => $data['danh_muc_id'],
            'gia'            => $data['gia'],
            'gia_khuyen_mai' => $data['gia_khuyen_mai'] ?? null,
            'so_luong'       => $totalQty,
        ]);


        $this->variantService->createVariants($product, $data['variants']);


        return response()->json([
            'data'    => new ProductResource($product),
            'status'  => 201,
            'message' => 'Tạo sản phẩm thành công',
        ]);
    }



    public function show($id)
    {
        $product = Product::findOrFail($id);

        return response()->json([
            'data'    => new ProductResource($product),
            'status'  => 200,
            'message' => 'Hiển thị chi tiết sản phẩm thành công',
        ]);
    }



    public function update(UpdateProductRequest $request, $id)
    {
        $product = Product::with('variants.size', 'variants.color')->findOrFail($id);
        $data    = $request->validated();

        
        if ($request->hasFile('hinh_anh')) {
            foreach (json_decode($product->hinh_anh ?? '[]', true) as $oldPath) {
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $newPaths = [];
            foreach ($request->file('hinh_anh') as $img) {
                $newPaths[] = $img->store('products', 'public');
            }

            $product->hinh_anh = json_encode($newPaths);
        }

        
        $isDefaultVariant = $product->variants->count() === 1 &&
            optional($product->variants->first()->size)->kich_co === 'DEFAULT' &&
            optional($product->variants->first()->color)->ten_mau_sac === 'DEFAULT';

        if ($isDefaultVariant) {
            
            $product->update([
                'ten'            => $data['ten'],
                'gia'            => $data['gia'],
                'gia_khuyen_mai' => $data['gia_khuyen_mai'] ?? null,
                'mo_ta'          => $data['mo_ta'] ?? null,
                'danh_muc_id'    => $data['danh_muc_id'],
                'so_luong'       => $data['so_luong'],
            ]);

            
            $product->variants->first()->update([
                'so_luong' => $data['so_luong']
            ]);
        } else {
            
            $product->update([
                'ten'            => $data['ten'],
                'gia'            => $data['gia'],
                'gia_khuyen_mai' => $data['gia_khuyen_mai'] ?? null,
                'mo_ta'          => $data['mo_ta'] ?? null,
                'danh_muc_id'    => $data['danh_muc_id'],
                
            ]);

            
            $totalQty = $product->variants()->sum('so_luong');
            $product->update(['so_luong' => $totalQty]);
        }

        return response()->json([
            'data'    => new ProductResource($product),
            'status'  => 200,
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


        foreach (json_decode($product->hinh_anh ?? '[]', true) as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }


        $variants = Variant::onlyTrashed()
            ->where('san_pham_id', $product->id)
            ->get();


        foreach ($variants as $variant) {
            foreach (json_decode($variant->hinh_anh ?? '[]', true) as $img) {
                if (Storage::disk('public')->exists($img)) {
                    Storage::disk('public')->delete($img);
                }
            }
        }


        Variant::onlyTrashed()
            ->where('san_pham_id', $product->id)
            ->forceDelete();


        $product->forceDelete();


        return response()->json([
            'status'  => 200,
            'message' => 'Đã xóa vĩnh viễn sản phẩm cùng toàn bộ ảnh & biến thể.',
        ]);
    }
}
