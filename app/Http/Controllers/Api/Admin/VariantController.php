<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Variant;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\VariantResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreVariantRequest;
use App\Http\Requests\UpdateVariantRequest;

class VariantController extends Controller
{
    public function show($id)
    {
        $variant = Variant::with('attributeValues.attribute')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Lấy chi tiết biến thể thành công.',
            'data' => new VariantResource($variant)
        ]);
    }

    public function getByProductId($productId)
    {
        $variants = Variant::with('attributeValues.attribute')
            ->where('san_pham_id', $productId)
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Lấy danh sách biến thể theo sản phẩm thành công.',
            'data' => VariantResource::collection($variants)
        ]);
    }

    public function store(StoreVariantRequest $request, $productId)
    {
        $validated = $request->validated();
        $duplicateAttributeTypes = collect($validated['attributes'])
            ->pluck('thuoc_tinh_id')
            ->duplicates();

        if ($duplicateAttributeTypes->isNotEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể chọn nhiều giá trị cho cùng một loại thuộc tính.',
            ], 422);
        }

        $inputKey = $this->buildAttributeKey($validated['attributes']);
        $existingVariants = Variant::where('san_pham_id', $productId)->with('attributeValues')->get();

        foreach ($existingVariants as $variant) {
            $existingKey = $this->buildAttributeKeyFromModel($variant);
            if ($existingKey === $inputKey) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Biến thể với tổ hợp thuộc tính này đã tồn tại.',
                ], 422);
            }
        }

        $variant = DB::transaction(function () use ($validated, $productId, $request) {
            $images = $this->uploadImages($request->file('images'));

            $variant = Variant::create([
                'san_pham_id'     => $productId,
                'so_luong'        => $validated['so_luong'],
                'gia'             => $validated['gia'],
                'gia_khuyen_mai'  => $validated['gia_khuyen_mai'] ?? null,
                'hinh_anh'        => $images,
            ]);

            foreach ($validated['attributes'] as $attribute) {
                $value = $this->resolveAttributeValue($attribute);
                $variant->attributeValues()->attach($value->id);
            }

            return $variant->load('attributeValues.attribute');
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Tạo biến thể thành công.',
            'data' => new VariantResource($variant)
        ]);
    }

    public function update(UpdateVariantRequest $request, $id)
    {
        $variant = Variant::withTrashed()->findOrFail($id);
        $validated = $request->validated();

        if (isset($validated['attributes'])) {
            $inputKey = $this->buildAttributeKey($validated['attributes']);

            $existingVariants = Variant::where('san_pham_id', $variant->san_pham_id)
                ->where('id', '!=', $variant->id)
                ->with('attributeValues')->get();

            foreach ($existingVariants as $existingVariant) {
                $existingKey = $this->buildAttributeKeyFromModel($existingVariant);
                if ($existingKey === $inputKey) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Biến thể với tổ hợp thuộc tính này đã tồn tại.',
                    ], 422);
                }
            }
        }

        if ($request->hasFile('images')) {
            if (is_array($variant->hinh_anh)) {
                foreach ($variant->hinh_anh as $oldImage) {
                    if (Storage::disk('public')->exists($oldImage)) {
                        Storage::disk('public')->delete($oldImage);
                    }
                }
            }
            $newImages = $this->uploadImages($request->file('images'));
            $validated['hinh_anh'] = $newImages;
        }

        $variant->update($validated);

        if (isset($validated['attributes'])) {
            $attributeValueIds = [];
            foreach ($validated['attributes'] as $attribute) {
                $value = $this->resolveAttributeValue($attribute);
                $attributeValueIds[] = $value->id;
            }
            $variant->attributeValues()->sync($attributeValueIds);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật biến thể thành công.',
            'data' => new VariantResource($variant->load('attributeValues.attribute'))
        ]);
    }

    public function destroy($id)
    {
        $variant = Variant::findOrFail($id);
        $variant->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Đã xóa mềm biến thể.',
        ]);
    }

    public function deleteByProductId($productId)
    {
        Variant::where('san_pham_id', $productId)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Đã xóa mềm tất cả biến thể của sản phẩm.',
        ]);
    }

    public function restore($id)
    {
        $variant = Variant::onlyTrashed()->findOrFail($id);
        $variant->restore();

        return response()->json([
            'status' => 'success',
            'message' => 'Khôi phục biến thể thành công.',
            'data' => new VariantResource($variant->load('attributeValues.attribute'))
        ]);
    }

    public function restoreByProductId($productId)
    {
        Variant::onlyTrashed()->where('san_pham_id', $productId)->restore();

        return response()->json([
            'status' => 'success',
            'message' => 'Khôi phục tất cả biến thể đã xóa mềm của sản phẩm.',
        ]);
    }

    public function getDeletedByProductId($productId)
    {
        $variants = Variant::onlyTrashed()
            ->with('attributeValues.attribute')
            ->where('san_pham_id', $productId)
            ->get();

        if ($variants->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy biến thể đã xóa mềm.',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Lấy danh sách biến thể đã xóa mềm thành công.',
            'data' => VariantResource::collection($variants)
        ]);
    }

    public function forceDelete($id)
    {
        $variant = Variant::onlyTrashed()->findOrFail($id);
        $variant->forceDelete();

        return response()->json([
            'status' => 'success',
            'message' => 'Xóa vĩnh viễn biến thể thành công.',
        ]);
    }

    public function forceDeleteByProductId($productId)
    {
        Variant::onlyTrashed()->where('san_pham_id', $productId)->forceDelete();

        return response()->json([
            'status' => 'success',
            'message' => 'Xóa vĩnh viễn tất cả biến thể đã xóa mềm của sản phẩm.',
        ]);
    }


    protected function uploadImages($images)
    {
        if (!$images) return [];
        return array_map(fn($image) => $image->store('variants', 'public'), $images);
    }

    protected function resolveAttributeValue($attribute)
    {
        if (!empty($attribute['attribute_value_id'])) {
            return AttributeValue::findOrFail($attribute['attribute_value_id']);
        }

        return AttributeValue::firstOrCreate([
            'thuoc_tinh_id' => $attribute['thuoc_tinh_id'],
            'gia_tri'       => trim($attribute['gia_tri']),
        ]);
    }

    protected function buildAttributeKey(array $attributes): string
    {
        return collect($attributes)->map(function ($attr) {
            $value = !empty($attr['attribute_value_id'])
                ? AttributeValue::findOrFail($attr['attribute_value_id'])
                : AttributeValue::where('thuoc_tinh_id', $attr['thuoc_tinh_id'])
                ->whereRaw('LOWER(gia_tri) = ?', [strtolower(trim($attr['gia_tri']))])
                ->firstOrFail();

            return $attr['thuoc_tinh_id'] . ':' . strtolower(trim($value->gia_tri));
        })->sort()->values()->implode(',');
    }

    protected function buildAttributeKeyFromModel(Variant $variant): string
    {
        return $variant->attributeValues->map(function ($val) {
            return $val->thuoc_tinh_id . ':' . strtolower(trim($val->gia_tri));
        })->sort()->values()->implode(',');
    }
}
