<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\BienThe;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
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
        'items.*.bien_the_id' => 'nullable|exists:biens,id',
    ]);

    DB::beginTransaction();

    try {
        $donHang = Order::create([
            'ma_don_hang' => 'DH' . strtoupper(Str::random(6)),
            'user_id' => $validated['user_id'],
            'phuong_thuc_thanh_toan_id' => $validated['phuong_thuc_thanh_toan_id'],
            'trang_thai_don_hang' => 'cho_xac_nhan',
            'trang_thai_thanh_toan' => 'cho_xu_ly',
        ]);

        foreach ($validated['items'] as $item) {
            $soLuong = $item['so_luong'];
            $bienTheId = $item['bien_the_id'] ?? null;

            if ($bienTheId) {
                $bienThe = BienThe::findOrFail($bienTheId);

                // Kiểm tra tồn kho biến thể
                if ($bienThe->so_luong < $soLuong) {
                    throw new \Exception("Biến thể sản phẩm không đủ hàng tồn kho.");
                }

                $donGia = $bienThe->gia_khuyen_mai ?? $bienThe->gia;
                $tongTien = $donGia * $soLuong;

                OrderDetail::create([
                    'don_hang_id' => $donHang->id,
                    'san_pham_id' => $item['san_pham_id'],
                    'bien_the_id' => $bienTheId,
                    'so_luong' => $soLuong,
                    'don_gia' => $donGia,
                    'tong_tien' => $tongTien,
                ]);

                // Cập nhật tồn kho biến thể
                $bienThe->decrement('so_luong', $soLuong);
                $bienThe->increment('so_luong_da_ban', $soLuong);

            } else {
                $sanPham = Product::findOrFail($item['san_pham_id']);

                // Kiểm tra tồn kho sản phẩm gốc
                if ($sanPham->so_luong < $soLuong) {
                    throw new \Exception("Sản phẩm '{$sanPham->ten}' không đủ hàng tồn kho.");
                }

                $donGia = $sanPham->gia_khuyen_mai ?? $sanPham->gia;
                $tongTien = $donGia * $soLuong;

                OrderDetail::create([
                    'don_hang_id' => $donHang->id,
                    'san_pham_id' => $sanPham->id,
                    'so_luong' => $soLuong,
                    'don_gia' => $donGia,
                    'tong_tien' => $tongTien,
                ]);

                $sanPham->decrement('so_luong', $soLuong);
                $sanPham->increment('so_luong_da_ban', $soLuong);
            }
        }

        DB::commit();

        return response()->json([
            'message' => 'Đặt hàng thành công!',
            'ma_don_hang' => $donHang->ma_don_hang,
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