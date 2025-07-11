<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use App\Http\Resources\AttributeValueResource;

class AttributeValueController extends Controller
{
    public function getByAttributeId($attributeId)
    {
        $attribute = Attribute::find($attributeId);

        if (!$attribute) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy thuộc tính.',
            ], 404);
        }

        $values = AttributeValue::where('thuoc_tinh_id', $attributeId)
            ->with(['attribute', 'variants'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Lấy danh sách giá trị thuộc tính theo thuộc tính thành công',
            'data'    => AttributeValueResource::collection($values)
        ]);
    }
    public function index()
    {
        $values = AttributeValue::with(['attribute', 'variants'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Danh sách tất cả giá trị thuộc tính',
            'data'    => AttributeValueResource::collection($values)
        ]);
    }

    public function store(Request $request, $attributeId)
    {
        $attribute = Attribute::find($attributeId);
        if (!$attribute) {
            return response()->json([
                'message' => 'Không tìm thấy thuộc tính.',
            ], 404);
        }

        $validated = $request->validate([
            'gia_tri' => [
                'required',
                'string',
                'max:255',
                'unique:gia_tri_thuoc_tinhs,gia_tri,NULL,id,thuoc_tinh_id,' . $attributeId,
            ],
        ], [
            'gia_tri.required' => 'Giá trị thuộc tính không được để trống.',
            'gia_tri.string'   => 'Giá trị phải là chuỗi.',
            'gia_tri.max'      => 'Tối đa 255 ký tự.',
            'gia_tri.unique'   => 'Giá trị đã tồn tại trong thuộc tính này.',
        ]);

        $value = AttributeValue::create([
            'thuoc_tinh_id' => $attributeId,
            'gia_tri'       => trim($validated['gia_tri']),
        ]);

        return response()->json([
            'message' => 'Thêm giá trị thành công',
            'data'    => new AttributeValueResource($value->load('attribute', 'variants'))
        ]);
    }

    public function show($id)
    {
        $value = AttributeValue::with(['attribute', 'variants'])->find($id);

        if (!$value) {
            return response()->json([
                'message' => 'Không tìm thấy giá trị thuộc tính',
                'data'    => null
            ], 404);
        }

        return response()->json([
            'message' => 'Lấy chi tiết giá trị thuộc tính thành công',
            'data'    => new AttributeValueResource($value)
        ]);
    }

    public function update(Request $request, $id)
    {
        $value = AttributeValue::find($id);
        if (!$value) {
            return response()->json([
                'message' => 'Không tìm thấy giá trị thuộc tính',
            ], 404);
        }

        $validated = $request->validate([
            'gia_tri' => [
                'required',
                'string',
                'max:255',
                'unique:gia_tri_thuoc_tinhs,gia_tri,' . $value->id . ',id,thuoc_tinh_id,' . $value->thuoc_tinh_id,
            ],
        ], [
            'gia_tri.required' => 'Giá trị không được để trống.',
            'gia_tri.string'   => 'Phải là chuỗi.',
            'gia_tri.max'      => 'Tối đa 255 ký tự.',
            'gia_tri.unique'   => 'Giá trị đã tồn tại.',
        ]);

        $value->update([
            'gia_tri' => trim($validated['gia_tri']),
        ]);

        return response()->json([
            'message' => 'Cập nhật thành công',
            'data'    => new AttributeValueResource($value->load('attribute', 'variants'))
        ]);
    }

    public function destroy($id)
    {
        $value = AttributeValue::with('variants')->find($id);

        if (!$value) {
            return response()->json([
                'message' => 'Không tìm thấy giá trị thuộc tính',
            ], 404);
        }

        if ($value->variants->count() > 0) {
            return response()->json([
                'message' => 'Không thể xóa vì đang sử dụng trong biến thể',
            ], 422);
        }

        $value->delete();

        return response()->json([
            'message' => 'Đã xóa mềm giá trị thành công',
        ]);
    }

    public function trash()
    {
        $trashed = AttributeValue::onlyTrashed()
            ->with('attribute')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Danh sách giá trị đã bị xóa mềm',
            'data'    => AttributeValueResource::collection($trashed)
        ]);
    }

    public function restore($id)
    {
        $value = AttributeValue::onlyTrashed()->find($id);

        if (!$value) {
            return response()->json([
                'message' => 'Không tìm thấy giá trị thuộc tính đã xóa',
            ], 404);
        }

        $value->restore();

        return response()->json([
            'message' => 'Khôi phục thành công',
            'data'    => new AttributeValueResource($value->load('attribute', 'variants'))
        ]);
    }
}
