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
use Illuminate\Support\Facades\Mail;

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

        // Tạo đơn hàng
        $order = Order::create([
            'ma_don_hang' => 'DH' . strtoupper(Str::random(6)),
            'user_id' => $validated['user_id'],
            'phuong_thuc_thanh_toan_id' => $validated['phuong_thuc_thanh_toan_id'],
            'trang_thai_don_hang' => 'cho_xac_nhan',
            'trang_thai_thanh_toan' => 'cho_xu_ly',
            'dia_chi' => $diaChi,
            'so_tien_thanh_toan' => 0,
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
                    'thuoc_tinh_bien_the' => $thuocTinhBienThe ? json_encode($thuocTinhBienThe) : null,
                ]);

                $sanPham->decrement('so_luong', $soLuong);
                $sanPham->increment('so_luong_da_ban', $soLuong);
            }
        }

        $order->update([
            'so_tien_thanh_toan' => $tongTienDonHang
        ]);

        DB::commit();

        // ✅ Gửi mail xác nhận
        try {
            Mail::to($user->email)->send(new \App\Mail\OrderConfirmationMail($order));
        } catch (\Exception $e) {
            Log::error('Lỗi gửi mail xác nhận đơn hàng: ' . $e->getMessage());
        }

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

public function storeFromCart(Request $request)
{
    $validated = $request->validate([
        'user_id' => 'required|exists:users,id',
        'phuong_thuc_thanh_toan_id' => 'required|exists:phuong_thuc_thanh_toans,id',
        'dia_chi' => 'nullable|string'
    ]);

    DB::beginTransaction();

    try {
        $user = User::findOrFail($validated['user_id']);
        $diaChi = $validated['dia_chi'] ?? $user->dia_chi;

        $gioHang = DB::table('gio_hangs')
            ->where('user_id', $validated['user_id'])
            ->first();

        if (!$gioHang) {
            return response()->json(['error' => 'Không tìm thấy giỏ hàng.'], 404);
        }

        $cartItems = DB::table('chi_tiet_gio_hangs')
            ->where('gio_hang_id', $gioHang->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['error' => 'Giỏ hàng đang trống.'], 400);
        }

        $tongTienDonHang = 0;

        $order = Order::create([
            'ma_don_hang' => 'DH' . strtoupper(Str::random(6)),
            'user_id' => $validated['user_id'],
            'phuong_thuc_thanh_toan_id' => $validated['phuong_thuc_thanh_toan_id'],
            'trang_thai_don_hang' => 'cho_xac_nhan',
            'trang_thai_thanh_toan' => 'cho_xu_ly',
            'dia_chi' => $diaChi,
            'so_tien_thanh_toan' => 0,
        ]);

        foreach ($cartItems as $item) {
            $soLuong = $item->so_luong;
            $bienTheId = $item->bien_the_id;

            $thuocTinhBienThe = null;
            $donGia = 0;
            $tongTien = 0;

            if ($bienTheId) {
                $bienThe = Variant::with(['variantAttributes.attributeValue.attribute'])
                    ->findOrFail($bienTheId);

                if ($bienThe->so_luong < $soLuong) {
                    throw new \Exception("Biến thể không đủ tồn kho.");
                }

                $donGia = $bienThe->gia_khuyen_mai ?? $bienThe->gia;
                $tongTien = $donGia * $soLuong;
                $tongTienDonHang += $tongTien;

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
                    'san_pham_id' => $item->san_pham_id,
                    'bien_the_id' => $bienTheId,
                    'so_luong' => $soLuong,
                    'don_gia' => $donGia,
                    'tong_tien' => $tongTien,
                    'thuoc_tinh_bien_the' => $thuocTinhBienThe->isEmpty() ? null : json_encode($thuocTinhBienThe),
                ]);

                $bienThe->decrement('so_luong', $soLuong);
                $bienThe->increment('so_luong_da_ban', $soLuong);
            } else {
                $sanPham = Product::findOrFail($item->san_pham_id);

                if ($sanPham->so_luong < $soLuong) {
                    throw new \Exception("Sản phẩm '{$sanPham->ten}' không đủ tồn kho.");
                }

                $donGia = $sanPham->gia_khuyen_mai ?? $sanPham->gia;
                $tongTien = $donGia * $soLuong;
                $tongTienDonHang += $tongTien;

                OrderDetail::create([
                    'don_hang_id' => $order->id,
                    'san_pham_id' => $item->san_pham_id,
                    'bien_the_id' => null,
                    'so_luong' => $soLuong,
                    'don_gia' => $donGia,
                    'tong_tien' => $tongTien,
                    'thuoc_tinh_bien_the' => null,
                ]);

                $sanPham->decrement('so_luong', $soLuong);
                $sanPham->increment('so_luong_da_ban', $soLuong);
            }
        }

        $order->update(['so_tien_thanh_toan' => $tongTienDonHang]);

        // Xoá giỏ hàng
        DB::table('chi_tiet_gio_hangs')->where('gio_hang_id', $gioHang->id)->delete();
        DB::table('gio_hangs')->where('id', $gioHang->id)->delete();

        DB::commit();

        // ✅ Gửi mail xác nhận đơn hàng
        try {
            Mail::to($user->email)->send(new \App\Mail\OrderConfirmationMail($order));
        } catch (\Exception $e) {
            Log::error('Lỗi gửi mail đơn hàng từ giỏ: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Đặt hàng từ giỏ thành công!',
            'ma_don_hang' => $order->ma_don_hang,
            'tong_tien' => $tongTienDonHang
        ], 201);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Lỗi đặt hàng từ giỏ', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return response()->json(['error' => 'Lỗi: ' . $e->getMessage()], 500);
    }
}

public function show($id)
{
    try {
        $order = Order::with([
            'orderDetail.product',
            'orderDetail.variant.variantAttributes.attributeValue.attribute',
            'paymentMethod',
            'user'
        ])->findOrFail($id);

        $orderDetail = $order->orderDetail->map(function ($detail) {
            $thuocTinhBienThe = null;

            if ($detail->bien_the_id && $detail->variant && $detail->variant->variantAttributes) {
                $thuocTinhBienThe = $detail->variant->variantAttributes->map(function ($attr) {
                    if (!$attr->attributeValue || !$attr->attributeValue->attribute) {
                        return null;
                    }
                    return [
                        'thuoc_tinh_id' => $attr->attributeValue->attribute->id,
                        'ten_thuoc_tinh' => $attr->attributeValue->attribute->ten,
                        'gia_tri' => $attr->attributeValue->gia_tri,
                    ];
                })->filter()->values();
            }

            return [
                'san_pham_id' => $detail->san_pham_id,
                'ten_san_pham' => optional($detail->product)->ten,
                'bien_the_id' => $detail->bien_the_id,
                'thuoc_tinh_bien_the' => $thuocTinhBienThe,
                'so_luong' => $detail->so_luong,
                'don_gia' => $detail->don_gia,
                'tong_tien' => $detail->tong_tien,
            ];
        });

        return response()->json([
            'order' => [
                'ma_don_hang' => $order->ma_don_hang,
                'user' => [
                    'id' => $order->user->id,
                    'ten' => $order->user->name,
                    'email' => $order->user->email,
                ],
                'dia_chi' => $order->dia_chi,
                'phuong_thuc_thanh_toan' => optional($order->paymentMethod)->ten,
                'trang_thai_don_hang' => $order->trang_thai_don_hang,
                'trang_thai_thanh_toan' => $order->trang_thai_thanh_toan,
                'so_tien_thanh_toan' => $order->so_tien_thanh_toan,
                'created_at' => $order->created_at,
                'items' => $orderDetail,
            ]
        ]);
    } catch (\Exception $e) {
        Log::error('Lỗi lấy chi tiết đơn hàng', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return response()->json(['error' => 'Lỗi: ' . $e->getMessage()], 500);
    }
}

    
}
