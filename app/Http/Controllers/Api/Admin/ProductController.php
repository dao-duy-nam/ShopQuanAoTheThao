<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Product;
use App\Models\Variant;
use Illuminate\Http\Request;
use App\Models\AttributeValue;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;

class ProductController extends Controller
{
    
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


        $imagePath = $request->hasFile('hinh_anh')
            ? $request->file('hinh_anh')->store('products', 'public')
            : null;


        $product = Product::create([
            'ten'         => $data['ten'],
            'mo_ta'       => $data['mo_ta'] ?? null,
            'hinh_anh'    => $imagePath,
            'danh_muc_id' => $data['danh_muc_id'],
            'so_luong'    => 0,
        ]);


        $tongSoLuong = 0;

        foreach ($data['variants'] as $i => $variantData) {

            $variantImage = null;
            if ($request->hasFile("variants.$i.hinh_anh")) {
                $variantImage = $request->file("variants.$i.hinh_anh")->store('variants', 'public');
            }



            $variant = Variant::create([
                'san_pham_id'    => $product->id,
                'so_luong'       => $variantData['so_luong'],
                'gia'            => $variantData['gia'],
                'gia_khuyen_mai' => $variantData['gia_khuyen_mai'] ?? null,
                'hinh_anh'       => $variantImage,
            ]);

            $tongSoLuong += $variantData['so_luong'];


            if (!empty($variantData['attributes']) && is_array($variantData['attributes'])) {
                $used = [];
                foreach ($variantData['attributes'] as $attr) {
                    if (in_array($attr['thuoc_tinh_id'], $used)) {
                        return response()->json([
                            'status'  => 'error',
                            'message' => "Biến thể #" . ($i + 1) . " trùng thuộc tính ID {$attr['thuoc_tinh_id']}",
                        ], 422);
                    }
                    $used[] = $attr['thuoc_tinh_id'];

                    $value = AttributeValue::firstOrCreate([
                        'thuoc_tinh_id' => $attr['thuoc_tinh_id'],
                        'gia_tri'       => $attr['gia_tri'],
                    ]);
                    $variant->attributeValues()->attach($value->id);
                }
            }
        }


        $product->update(['so_luong' => $tongSoLuong]);


        return response()->json([
            'status'  => 'success',
            'message' => 'Tạo sản phẩm & biến thể thành công.',
            'data'    => new ProductResource(
                $product->load('variants.attributeValues.attribute')
            ),
        ]);
    }



    public function show($id)
    {

        $product = Product::with([
            'variants.attributeValues.attribute'
        ])->findOrFail($id);

        return response()->json([
            'data'    => new ProductResource($product),
            'status'  => 200,
            'message' => 'Hiển thị chi tiết sản phẩm thành công',
        ]);
    }


    public function update(UpdateProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $data    = $request->validated();


        $imagePath = $product->hinh_anh;
        if ($request->hasFile('hinh_anh')) {

            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            $imagePath = $request->file('hinh_anh')->store('products', 'public');
        }


        $product->update([
            'ten'         => $data['ten'],
            'mo_ta'       => $data['mo_ta'] ?? null,
            'danh_muc_id' => $data['danh_muc_id'],
            'hinh_anh'    => $imagePath,
        ]);


        return response()->json([
            'data'    => new ProductResource(
                $product->refresh()->load('variants.attributeValues.attribute')
            ),
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


        if ($product->hinh_anh && Storage::disk('public')->exists($product->hinh_anh)) {
            Storage::disk('public')->delete($product->hinh_anh);
        }


        $variants = Variant::onlyTrashed()
            ->where('san_pham_id', $product->id)
            ->get();

        foreach ($variants as $variant) {
            if ($variant->hinh_anh && Storage::disk('public')->exists($variant->hinh_anh)) {
                Storage::disk('public')->delete($variant->hinh_anh);
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
