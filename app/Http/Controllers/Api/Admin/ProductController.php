<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        // Tìm kiếm
        if ($request->has('keyword')) {
            $query->where('ten', 'like', '%' . $request->keyword . '%');
        }

        // Ẩn sản phẩm đã bị xóa mềm
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
            'gia'             => 'required|numeric|min:0',
            'gia_khuyen_mai'  => 'nullable|numeric|min:0',
            'so_luong'        => 'required|integer|min:0',
            'mo_ta'           => 'nullable|string',
            'hinh_anh'        => 'nullable|string|url',
            'danh_muc_id'     => 'required|exists:danh_mucs,id',
        ], [
            'ten.required'             => 'Tên sản phẩm không được để trống.',
            'ten.string'               => 'Tên sản phẩm phải là chuỗi ký tự.',
            'ten.max'                  => 'Tên sản phẩm không được vượt quá 255 ký tự.',
            'gia.required'             => 'Giá sản phẩm không được để trống.',
            'gia.numeric'              => 'Giá sản phẩm phải là số.',
            'gia.min'                  => 'Giá sản phẩm không được nhỏ hơn 0.',
            'gia_khuyen_mai.numeric'  => 'Giá khuyến mãi phải là số.',
            'gia_khuyen_mai.min'      => 'Giá khuyến mãi không được nhỏ hơn 0.',
            'so_luong.required'       => 'Số lượng sản phẩm không được để trống.',
            'so_luong.integer'        => 'Số lượng sản phẩm phải là số nguyên.',
            'so_luong.min'            => 'Số lượng sản phẩm không được nhỏ hơn 0.',
            'mo_ta.string'            => 'Mô tả sản phẩm phải là chuỗi ký tự.',
            'hinh_anh.string'         => 'Hình ảnh phải là chuỗi ký tự.',
            'hinh_anh.url'            => 'Hình ảnh phải là đường dẫn hợp lệ.',
            'danh_muc_id.required'    => 'Danh mục sản phẩm không được để trống.',
            'danh_muc_id.exists'      => 'Danh mục sản phẩm không hợp lệ.',
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->hasFile('hinh_anh')) {
            $imagePath = $request->file('hinh_anh')->store('products', 'public');
            $dataValidate['hinh_anh'] = $imagePath;
        }
        $product = Product::create($request->all());

        return response()->json([
            'data' => new ProductResource($product),
            'status' => 201,
            'message' => 'Tạo sản phẩm thành công',
        ]);
    }

    public function show($id)
    {
        $product = Product::withTrashed()->findOrFail($id);

        return response()->json([
            'data' => new ProductResource($product),
            'status' => 200,
            'message' => 'Hiển thị chi tiết sản phẩm thành công',
        ]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::withTrashed()->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'ten'             => 'required|string|max:255',
            'gia'             => 'required|numeric|min:0',
            'gia_khuyen_mai'  => 'nullable|numeric|min:0',
            'so_luong'        => 'required|integer|min:0',
            'mo_ta'           => 'nullable|string',
            'hinh_anh'        => 'nullable|string|url',
            'danh_muc_id'     => 'required|exists:danh_mucs,id',
        ], [
            'ten.required'             => 'Tên sản phẩm không được để trống.',
            'ten.string'               => 'Tên sản phẩm phải là chuỗi ký tự.',
            'ten.max'                  => 'Tên sản phẩm không được vượt quá 255 ký tự.',
            'gia.required'             => 'Giá sản phẩm không được để trống.',
            'gia.numeric'              => 'Giá sản phẩm phải là số.',
            'gia.min'                  => 'Giá sản phẩm không được nhỏ hơn 0.',
            'gia_khuyen_mai.numeric'  => 'Giá khuyến mãi phải là số.',
            'gia_khuyen_mai.min'      => 'Giá khuyến mãi không được nhỏ hơn 0.',
            'so_luong.required'       => 'Số lượng sản phẩm không được để trống.',
            'so_luong.integer'        => 'Số lượng sản phẩm phải là số nguyên.',
            'so_luong.min'            => 'Số lượng sản phẩm không được nhỏ hơn 0.',
            'mo_ta.string'            => 'Mô tả sản phẩm phải là chuỗi ký tự.',
            'hinh_anh.string'         => 'Hình ảnh phải là chuỗi ký tự.',
            'hinh_anh.url'            => 'Hình ảnh phải là đường dẫn hợp lệ.',
            'danh_muc_id.required'    => 'Danh mục sản phẩm không được để trống.',
            'danh_muc_id.exists'      => 'Danh mục sản phẩm không hợp lệ.',
        ]);



        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->hasFile('hinh_anh')) {
            $imagePath = $request->file('hinh_anh')->store('products', 'public');
            $dataValidate['hinh_anh'] = $imagePath;

            if ($product->hinh_anh) {
                Storage::disk('public')->delete($product->hinh_anh);
            }
        }

        $product->update($request->all());

        return response()->json([
            'data' => new ProductResource($product),
            'status' => 200,
            'message' => 'Cập nhật sản phẩm thành công',
        ]);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        if ($product->hinh_anh) {
            Storage::disk('public')->delete($product->hinh_anh);
        }
        $product->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Xóa thành công',
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

        return response()->json([
            'status' => 200,
            'message' => 'Khôi phục sản phẩm thành công',
        ]);
    }

    public function forceDelete($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->forceDelete();

        return response()->json([
            'status' => 200,
            'message' => 'Đã xóa vĩnh viễn sản phẩm',
        ]);
    }
}
