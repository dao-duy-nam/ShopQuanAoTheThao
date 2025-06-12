<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Product;
use App\Models\Variant;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Cloudinary\Api\Upload\UploadApi;
use App\Http\Resources\ProductResource;
use App\Models\Color;
use App\Models\Size;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ten'             => 'required|string|max:255',
            'mo_ta'           => 'nullable|string',
            'hinh_anh'        => 'nullable|string|url',
            'danh_muc_id'     => 'required|exists:danh_mucs,id',
            'gia'             => 'required|numeric|min:0',
            'variants'        => 'required|array|min:1',
            'variants.*.kich_co'        => 'required|string|max:100',
            'variants.*.mau_sac'        => 'required|string|max:100',
            'variants.*.so_luong'       => 'required|integer|min:0',
            'variants.*.gia'            => 'required|numeric|min:0',
            'variants.*.gia_khuyen_mai' => 'nullable|numeric|min:0',
        ], [
            'ten.required' => 'Tên sản phẩm không được để trống.',
            'ten.string'   => 'Tên sản phẩm phải là chuỗi ký tự.',
            'ten.max'      => 'Tên sản phẩm không được vượt quá 255 ký tự.',
            'mo_ta.string' => 'Mô tả sản phẩm phải là chuỗi ký tự.',
            'hinh_anh.string' => 'Hình ảnh phải là chuỗi ký tự.',
            'hinh_anh.url' => 'Hình ảnh phải là đường dẫn hợp lệ.',
            'danh_muc_id.required' => 'Danh mục sản phẩm không được để trống.',
            'danh_muc_id.exists'   => 'Danh mục sản phẩm không hợp lệ.',
            'gia.required' => 'Giá sản phẩm không được để trống.',
            'gia.numeric'  => 'Giá sản phẩm phải là số.',
            'gia.min'      => 'Giá sản phẩm không được nhỏ hơn 0.',
            'variants.required'    => 'Phải có ít nhất một biến thể.',
            'variants.array'       => 'Biến thể phải là một mảng.',
            'variants.*.kich_co.required' => 'Kích cỡ biến thể không được để trống.',
            'variants.*.kich_co.string'   => 'Kích cỡ biến thể phải là chuỗi ký tự.',
            'variants.*.kich_co.max'      => 'Kích cỡ biến thể không được vượt quá 100 ký tự.',
            'variants.*.mau_sac.required' => 'Màu sắc biến thể không được để trống.',
            'variants.*.mau_sac.string'   => 'Màu sắc biến thể phải là chuỗi ký tự.',
            'variants.*.mau_sac.max'      => 'Màu sắc biến thể không được vượt quá 100 ký tự.',
            'variants.*.so_luong.required' => 'Số lượng biến thể không được để trống.',
            'variants.*.so_luong.integer'  => 'Số lượng biến thể phải là số nguyên.',
            'variants.*.so_luong.min'      => 'Số lượng biến thể không được nhỏ hơn 0.',
            'variants.*.gia.required'      => 'Giá biến thể không được để trống.',
            'variants.*.gia.numeric'       => 'Giá biến thể phải là số.',
            'variants.*.gia.min'           => 'Giá biến thể không được nhỏ hơn 0.',
            'variants.*.gia_khuyen_mai.numeric' => 'Giá khuyến mãi biến thể phải là số.',
            'variants.*.gia_khuyen_mai.min'     => 'Giá khuyến mãi biến thể không được nhỏ hơn 0.',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();
        $product = Product::create([
            'ten' => $data['ten'],
            'mo_ta' => $data['mo_ta'] ?? null,
            'hinh_anh' => $data['hinh_anh'] ?? null,
            'danh_muc_id' => $data['danh_muc_id'],
            'gia' => $data['gia'],
            'so_luong' => 0,
        ]);
        foreach ($data['variants'] as $variant) {
            $kichCo = Size::firstOrCreate(
                ['kich_co' => $variant['kich_co']],
                ['created_at' => now(), 'updated_at' => now()]
            );
            $mauSac = Color::firstOrCreate(
                ['ten_mau_sac' => $variant['mau_sac']],
                ['created_at' => now(), 'updated_at' => now()]
            );
            Variant::create([
                'san_pham_id' => $product->id,
                'kich_co_id' => $kichCo->id,
                'mau_sac_id' => $mauSac->id,
                'so_luong' => $variant['so_luong'],
                'gia' => $variant['gia'],
                'gia_khuyen_mai' => $variant['gia_khuyen_mai'] ?? null,
            ]);
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

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'ten'             => 'required|string|max:255',
            'gia'             => 'required|numeric|min:0',
            'gia_khuyen_mai'  => 'nullable|numeric|min:0',
            'so_luong'        => 'required|integer|min:0',
            'mo_ta'           => 'nullable|string',
            'hinh_anh'        => 'nullable|string|url',
            'hinh_anh_public_id' => 'nullable|string',
            'danh_muc_id'     => 'required|exists:danh_mucs,id',
            'variants'        => 'required|array|min:1',
            'variants.*.id'   => 'nullable|integer|exists:bien_thes,id',
            'variants.*.kich_co'        => 'required|string|max:100',
            'variants.*.mau_sac'        => 'required|string|max:100',
            'variants.*.so_luong'       => 'required|integer|min:0',
            'variants.*.gia'            => 'required|numeric|min:0',
            'variants.*.gia_khuyen_mai' => 'nullable|numeric|min:0',
            'deleted_variant_ids'       => 'nullable|array',
            'deleted_variant_ids.*' => 'integer|exists:bien_thes,id',
        ], [
            'ten.required' => 'Tên sản phẩm không được để trống.',
            'ten.string' => 'Tên sản phẩm phải là chuỗi.',
            'ten.max' => 'Tên sản phẩm không được vượt quá 255 ký tự.',
            'gia.required' => 'Giá sản phẩm không được để trống.',
            'gia.numeric' => 'Giá sản phẩm phải là số.',
            'gia.min' => 'Giá sản phẩm phải lớn hơn hoặc bằng 0.',
            'gia_khuyen_mai.numeric' => 'Giá khuyến mãi phải là số.',
            'gia_khuyen_mai.min' => 'Giá khuyến mãi phải lớn hơn hoặc bằng 0.',
            'so_luong.required' => 'Số lượng sản phẩm không được để trống.',
            'so_luong.integer' => 'Số lượng phải là số nguyên.',
            'so_luong.min' => 'Số lượng phải lớn hơn hoặc bằng 0.',
            'mo_ta.string' => 'Mô tả phải là chuỗi.',
            'hinh_anh.string' => 'Hình ảnh phải là chuỗi.',
            'hinh_anh.url' => 'Hình ảnh phải là đường dẫn hợp lệ.',
            'hinh_anh_public_id.string' => 'ID hình ảnh phải là chuỗi.',
            'danh_muc_id.required' => 'Danh mục sản phẩm không được để trống.',
            'danh_muc_id.exists' => 'Danh mục sản phẩm không hợp lệ.',
            'variants.required' => 'Phải có ít nhất một biến thể.',
            'variants.array' => 'Biến thể phải là một mảng.',
            'variants.*.id.integer' => 'ID biến thể phải là số nguyên.',
            'variants.*.id.exists' => 'ID biến thể không tồn tại.',
            'variants.*.kich_co.required' => 'Kích cỡ không được để trống.',
            'variants.*.kich_co.string' => 'Kích cỡ phải là chuỗi.',
            'variants.*.kich_co.max' => 'Kích cỡ không được vượt quá 100 ký tự.',
            'variants.*.mau_sac.required' => 'Màu sắc không được để trống.',
            'variants.*.mau_sac.string' => 'Màu sắc phải là chuỗi.',
            'variants.*.mau_sac.max' => 'Màu sắc không được vượt quá 100 ký tự.',
            'variants.*.so_luong.required' => 'Số lượng của biến thể không được để trống.',
            'variants.*.so_luong.integer' => 'Số lượng của biến thể phải là số nguyên.',
            'variants.*.so_luong.min' => 'Số lượng của biến thể phải lớn hơn hoặc bằng 0.',
            'variants.*.gia.required' => 'Giá của biến thể không được để trống.',
            'variants.*.gia.numeric' => 'Giá của biến thể phải là số.',
            'variants.*.gia.min' => 'Giá của biến thể phải lớn hơn hoặc bằng 0.',
            'variants.*.gia_khuyen_mai.numeric' => 'Giá khuyến mãi của biến thể phải là số.',
            'variants.*.gia_khuyen_mai.min' => 'Giá khuyến mãi của biến thể phải lớn hơn hoặc bằng 0.',
            'deleted_variant_ids.array' => 'Danh sách biến thể xóa phải là mảng.',
            'deleted_variant_ids.*.integer' => 'ID biến thể xóa phải là số nguyên.',
            'deleted_variant_ids.*.exists' => 'ID biến thể xóa không tồn tại.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();


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


        if (!empty($data['deleted_variant_ids'])) {
            Variant::whereIn('id', $data['deleted_variant_ids'])
                ->where('san_pham_id', $product->id)
                ->delete();
        }

        foreach ($data['variants'] as $variant) {
            $kichCo = Size::firstOrCreate(
                ['kich_co' => $variant['kich_co']],
                ['created_at' => now(), 'updated_at' => now()]
            );

            $mauSac = Color::firstOrCreate(
                ['ten_mau_sac' => $variant['mau_sac']],
                ['created_at' => now(), 'updated_at' => now()]
            );

            if (!empty($variant['id'])) {

                $variantModel = Variant::where('id', $variant['id'])
                    ->where('san_pham_id', $product->id)
                    ->first();

                if ($variantModel) {
                    $variantModel->update([
                        'kich_co_id' => $kichCo->id,
                        'mau_sac_id' => $mauSac->id,
                        'so_luong' => $variant['so_luong'],
                        'gia' => $variant['gia'],
                        'gia_khuyen_mai' => $variant['gia_khuyen_mai'] ?? null,
                    ]);
                }
            } else {

                Variant::create([
                    'san_pham_id' => $product->id,
                    'kich_co_id' => $kichCo->id,
                    'mau_sac_id' => $mauSac->id,
                    'so_luong' => $variant['so_luong'],
                    'gia' => $variant['gia'],
                    'gia_khuyen_mai' => $variant['gia_khuyen_mai'] ?? null,
                ]);
            }
        }

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
