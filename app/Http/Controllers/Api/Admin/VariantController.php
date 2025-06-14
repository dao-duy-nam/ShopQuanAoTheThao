<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Size;

use App\Models\Color;
use App\Models\Variant;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreVariantRequest;
use App\Http\Requests\UpdateVariantRequest;

class VariantController extends Controller
{
    public function show($id)
    {
        $variant = Variant::with(['Size', 'Color'])
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'message' => 'Lấy chi tiết biến thể thành công.',
            'data' => $variant
        ]);
    }

   public function getByProductId($productId)
{
    $variants = Variant::with(['Size', 'Color'])
        ->where('san_pham_id', $productId)
        ->get();

    return response()->json([
        'status' => 'success',
        'message' => 'Lấy danh sách biến thể theo sản phẩm thành công.',
        'data' => $variants
    ]);
}


    public function store(StoreVariantRequest $request, $productId)
    {
        $validated = $request->validated();

        $Size = Size::firstOrCreate(['kich_co' => $validated['kich_co']]);
        $Color = Color::firstOrCreate(['ten_mau_sac' => $validated['mau_sac']]);

        $hinhAnh = $this->uploadImages($request->file('images'));

        $variant = Variant::create([
            'san_pham_id' => $productId,
            'kich_co_id' => $Size->id,
            'mau_sac_id' => $Color->id,
            'so_luong' => $validated['so_luong'],
            'gia' => $validated['gia'],
            'gia_khuyen_mai' => $validated['gia_khuyen_mai'] ?? null,
            'hinh_anh' => $hinhAnh,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Tạo biến thể thành công.',
            'data' => $variant
        ]);
    }

    public function update(UpdateVariantRequest $request, $id)
    {
        $variant = Variant::withTrashed()->findOrFail($id);

        $validated = $request->validated();

        if ($request->hasFile('images')) {
            $newImages = $this->uploadImages($request->file('images'));

            $oldImages = $variant->hinh_anh ?? [];

            foreach ($newImages as $newImage) {
                if (count($oldImages) >= 4) {
                    $imageToDelete = array_shift($oldImages);
                    if (Storage::disk('public')->exists($imageToDelete)) {
                        Storage::disk('public')->delete($imageToDelete);
                    }
                }
                $oldImages[] = $newImage;
            }

            $validated['hinh_anh'] = $oldImages;
        }

        $variant->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật biến thể thành công.',
            'data' => $variant
        ]);
    }


    public function destroy($id)
    {
        $variant = Variant::find($id);
        if (!$variant) {
            return response()->json([
                'status' => 'error',
                'message' => 'Biến thể không tồn tại.',
                'data' => null
            ], 404);
        }

        $variant->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Xóa mềm biến thể thành công.',
            'data' => null
        ]);
    }

    public function deleteByProductId($productId)
    {
        $exists = Variant::where('san_pham_id', $productId)->exists();
        if (!$exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy biến thể nào của sản phẩm này.',
                'data' => null
            ], 404);
        }

        Variant::where('san_pham_id', $productId)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Xóa mềm tất cả biến thể của sản phẩm thành công.',
            'data' => null
        ]);
    }

    public function restore($id)
    {
        $variant = Variant::onlyTrashed()->find($id);
        if (!$variant) {
            return response()->json([
                'status' => 'error',
                'message' => 'Biến thể không tồn tại hoặc chưa bị xóa mềm.',
                'data' => null
            ], 404);
        }

        $variant->restore();

        return response()->json([
            'status' => 'success',
            'message' => 'Khôi phục biến thể thành công.',
            'data' => $variant
        ]);
    }

    public function restoreByProductId($productId)
    {
        $exists = Variant::onlyTrashed()->where('san_pham_id', $productId)->exists();
        if (!$exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không có biến thể bị xóa mềm của sản phẩm này để khôi phục.',
                'data' => null
            ], 404);
        }

        Variant::onlyTrashed()->where('san_pham_id', $productId)->restore();

        return response()->json([
            'status' => 'success',
            'message' => 'Khôi phục tất cả biến thể của sản phẩm thành công.',
            'data' => null
        ]);
    }
    public function getDeletedByProductId($productId)
    {
        $variants = Variant::onlyTrashed()
            ->with(['Size', 'Color'])
            ->where('san_pham_id', $productId)
            ->get();

        if ($variants->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không có biến thể bị xóa mềm của sản phẩm này.',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Lấy danh sách biến thể đã xóa mềm của sản phẩm thành công.',
            'data' => $variants
        ]);
    }

    public function forceDelete($id)
    {
        $variant = Variant::onlyTrashed()->find($id);
        if (!$variant) {
            return response()->json([
                'status' => 'error',
                'message' => 'Biến thể không tồn tại hoặc chưa bị xóa mềm.',
                'data' => null
            ], 404);
        }

        $variant->forceDelete();

        return response()->json([
            'status' => 'success',
            'message' => 'Xóa vĩnh viễn biến thể thành công.',
            'data' => null
        ]);
    }

    public function forceDeleteByProductId($productId)
    {
        $exists = Variant::onlyTrashed()->where('san_pham_id', $productId)->exists();
        if (!$exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không có biến thể bị xóa mềm của sản phẩm này để xóa vĩnh viễn.',
                'data' => null
            ], 404);
        }

        Variant::onlyTrashed()->where('san_pham_id', $productId)->forceDelete();

        return response()->json([
            'status' => 'success',
            'message' => 'Xóa vĩnh viễn tất cả biến thể của sản phẩm thành công.',
            'data' => null
        ]);
    }

    protected function uploadImages($images)
    {
        if (!$images) return [];

        $paths = [];

        foreach ($images as $image) {
            $path = $image->store('variants', 'public');
            $paths[] = $path;
        }

        return $paths;
    }
}
