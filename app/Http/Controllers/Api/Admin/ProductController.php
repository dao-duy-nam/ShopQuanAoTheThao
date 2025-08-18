<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Product;
use App\Models\Variant;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\AttributeValue;
use Illuminate\Support\Facades\DB;
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
        $query = Product::with('category');

        if ($request->filled('keyword')) {
            $query->where('ten', 'like', '%' . $request->keyword . '%');
        }


        if ($request->filled('ten_danh_muc')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('ten', 'like', '%' . $request->ten_danh_muc . '%');
            });
        }


        if ($request->filled('gia_tu')) {
            $query->where('gia', '>=', $request->gia_tu);
        }

        if ($request->filled('gia_den')) {
            $query->where('gia', '<=', $request->gia_den);
        }


        $products = $query->latest()->paginate(10);

        if ($products->isEmpty()) {
            return response()->json([
                'data' => [],
                'status' => 200,
                'message' => 'Không tìm thấy sản phẩm nào phù hợp.',
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

        if (empty($data['variants']) || count($data['variants']) === 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Mỗi sản phẩm phải có ít nhất một biến thể.',
            ], 422);
        }

        try {
            $product = DB::transaction(function () use ($request, $data) {

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
                $checkedCombinations = [];

                foreach ($data['variants'] as $i => $variantData) {

                    $images = [];
                    if ($request->hasFile("variants.$i.hinh_anh")) {
                        foreach ($request->file("variants.$i.hinh_anh") as $imageFile) {
                            $images[] = $imageFile->store('variants', 'public');
                        }
                    }

                    $attributeCombination = [];
                    $usedThuocTinh = [];
                    $attachedValueIds = [];

                    if (!empty($variantData['attributes']) && is_array($variantData['attributes'])) {
                        foreach ($variantData['attributes'] as $attr) {
                            if (empty($attr['thuoc_tinh_id'])) {
                                throw new \Exception("Thiếu thuộc tính ID ở biến thể #" . ($i + 1));
                            }

                            $thuocTinhId = $attr['thuoc_tinh_id'];
                            if (in_array($thuocTinhId, $usedThuocTinh)) {
                                throw new \Exception("Biến thể " . ($i + 1) . " bị trùng thuộc tính ID {$thuocTinhId}");
                            }
                            $usedThuocTinh[] = $thuocTinhId;

                            $giaTri = null;
                            if (!empty($attr['gia_tri_thuoc_tinh_id'])) {
                                $giaTri = AttributeValue::where('id', $attr['gia_tri_thuoc_tinh_id'])
                                    ->where('thuoc_tinh_id', $thuocTinhId)
                                    ->first();

                                if (!$giaTri) {
                                    throw new \Exception("Giá trị thuộc tính ID {$attr['gia_tri_thuoc_tinh_id']} không hợp lệ với thuộc tính ID {$thuocTinhId}");
                                }
                            } elseif (!empty($attr['gia_tri'])) {
                                $giaTri = AttributeValue::firstOrCreate([
                                    'thuoc_tinh_id' => $thuocTinhId,
                                    'gia_tri'       => $attr['gia_tri'],
                                ]);
                            } else {
                                throw new \Exception("Bạn phải chọn hoặc nhập giá trị cho thuộc tính ID {$thuocTinhId} ở biến thể #" . ($i + 1));
                            }


                            $attributeCombination[] = $thuocTinhId . ':' . Str::slug($giaTri->gia_tri);
                            $attachedValueIds[] = $giaTri->id;
                        }
                    }


                    sort($attributeCombination);
                    $combinationKey = implode(',', $attributeCombination);
                    if (in_array($combinationKey, $checkedCombinations)) {
                        throw new \Exception("Biến thể " . ($i + 1) . " bị trùng tổ hợp thuộc tính với biến thể khác.");
                    }
                    $checkedCombinations[] = $combinationKey;

                    $variant = Variant::create([
                        'san_pham_id'    => $product->id,
                        'so_luong'       => $variantData['so_luong'],
                        'gia'            => $variantData['gia'],
                        'gia_khuyen_mai' => $variantData['gia_khuyen_mai'] ?? null,
                        'hinh_anh'       => json_encode($images),
                    ]);

                    $tongSoLuong += $variantData['so_luong'];


                    if (!empty($attachedValueIds)) {
                        $variant->attributeValues()->attach($attachedValueIds);
                    }
                }


                $product->update(['so_luong' => $tongSoLuong]);

                return $product;
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Tạo sản phẩm & biến thể thành công.',
                'data'    => new ProductResource($product->load('variants.attributeValues.attribute')),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
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

    // public function forceDelete($id)
    // {
    //     $product = Product::onlyTrashed()->findOrFail($id);


    //     if ($product->hinh_anh && Storage::disk('public')->exists($product->hinh_anh)) {
    //         Storage::disk('public')->delete($product->hinh_anh);
    //     }


    //     $variants = Variant::onlyTrashed()
    //         ->where('san_pham_id', $product->id)
    //         ->get();

    //     foreach ($variants as $variant) {
    //         if ($variant->hinh_anh && Storage::disk('public')->exists($variant->hinh_anh)) {
    //             Storage::disk('public')->delete($variant->hinh_anh);
    //         }
    //     }


    //     Variant::onlyTrashed()
    //         ->where('san_pham_id', $product->id)
    //         ->forceDelete();

    //     $product->forceDelete();

    //     return response()->json([
    //         'status'  => 200,
    //         'message' => 'Đã xóa vĩnh viễn sản phẩm cùng toàn bộ ảnh & biến thể.',
    //     ]);
    // }
}
