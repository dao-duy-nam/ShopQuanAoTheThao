<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Variant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ClientOrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'phuong_thuc_thanh_toan_id' => 'required|exists:phuong_thuc_thanh_toans,id',
            'items' => 'required|array|min:1',
            'items.*.san_pham_id' => 'required|exists:san_phams,id',
            'items.*.so_luong' => 'required|integer|min:1',
            'items.*.bien_the_id' => 'nullable|exists:bien_thes,id',
            'dia_chi' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $user = User::findOrFail($validated['user_id']);
            $diaChi = $validated['dia_chi'] ?? $user->dia_chi;

            $tongTienDonHang = 0;

            // ✅ Tạo đơn hàng với tổng tiền mặc định = 0
            $order = Order::create([
                'ma_don_hang' => 'DH' . strtoupper(Str::random(6)),
                'user_id' => $validated['user_id'],
                'phuong_thuc_thanh_toan_id' => $validated['phuong_thuc_thanh_toan_id'],
                'trang_thai_don_hang' => 'cho_xac_nhan',
                'trang_thai_thanh_toan' => 'cho_xu_ly',
                'dia_chi' => $diaChi,
                'so_tien_thanh_toan' => 0,   // ✅ Fix lỗi bắt buộc field này
            ]);

            foreach ($validated['items'] as $item) {
                $soLuong = $item['so_luong'];
                $bienTheId = $item['bien_the_id'] ?? null;

                $thuocTinhBienThe = null;
                $donGia = 0;
                $tongTien = 0;

                if ($bienTheId) {
                    $bienThe = Variant::with(['variantAttributes.attributeValue.attribute'])->findOrFail($bienTheId);

                      if ($bienThe->so_luong < $soLuong) {
                        throw new \Exception("Biến thể sản phẩm không đủ tồn kho.");
                    }

                    $donGia = $bienThe->gia_khuyen_mai ?? $bienThe->gia;
                    $tongTien = $donGia * $soLuong;
                    $tongTienDonHang += $tongTien;

                    // ✅ Lấy thuộc tính biến thể
                    $thuocTinhBienThe = $bienThe->variantAttributes->map(function ($attribute) {
                        if (!$attribute->attributeValue || !$attribute->attributeValue->attribute) {
                            return null;
                        }
                        return [
                            'thuoc_tinh_id' => $attribute->attributeValue->attribute->id,
                            'gia_tri' => $attribute->attributeValue->gia_tri
                        ];
                    })->filter()->values();


                    OrderDetail::create([
                        'don_hang_id' => $order->id,
                        'san_pham_id' => $item['san_pham_id'],
                        'bien_the_id' => $bienTheId,
                        'so_luong' => $soLuong,
                        'don_gia' => $donGia,
                        'tong_tien' => $tongTien,
                        'thuoc_tinh_bien_the' => $thuocTinhBienThe->isEmpty() ? null : json_encode($thuocTinhBienThe),
                    ]);

                    $bienThe->decrement('so_luong', $soLuong);
                    $bienThe->increment('so_luong_da_ban', $soLuong);
                } else {
                    $sanPham = Product::findOrFail($item['san_pham_id']);

                    if ($sanPham->so_luong < $soLuong) {
                        throw new \Exception("Sản phẩm '{$sanPham->ten}' không đủ tồn kho.");
                    }

                    $donGia = $sanPham->gia_khuyen_mai ?? $sanPham->gia;
                    $tongTien = $donGia * $soLuong;
                    $tongTienDonHang += $tongTien;

                    OrderDetail::create([
                    'don_hang_id' => $order->id,
                    'san_pham_id' => $item['san_pham_id'],
                    'bien_the_id' => $bienTheId,
                    'so_luong' => $soLuong,
                    'don_gia' => $donGia,
                    'tong_tien' => $tongTien,
                    'thuoc_tinh_bien_the' => $thuocTinhBienThe ? json_encode($thuocTinhBienThe) : null,  // 👈 Lưu thuộc tính
                ]);

                    $sanPham->decrement('so_luong', $soLuong);
                    $sanPham->increment('so_luong_da_ban', $soLuong);
                }
            }


            $order->update([
                'so_tien_thanh_toan' => $tongTienDonHang
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Đặt hàng thành công!',
                'ma_don_hang' => $order->ma_don_hang,
                'tong_tien' => $tongTienDonHang
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi đặt hàng', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }
}
