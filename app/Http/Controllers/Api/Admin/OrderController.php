<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController
{
    // Danh sách đơn hàng
    public function index()
    {
        return response()->json(Order::with(['orderDetails', 'paymentMethod'])->get());
    }

    // Chi tiết đơn hàng
    public function show($id)
    {
        $order = Order::with(['orderDetails', 'paymentMethod'])->findOrFail($id);
        return response()->json($order);
    }

    // Tạo mới đơn hàng
    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'ma_don_hang' => 'required|unique:don_hangs,ma_don_hang',
    //         'nguoi_dung_id' => 'required|integer',
    //         'phuong_thuc_thanh_toan_id' => 'required|integer|exists:phuong_thuc_thanh_toans,id',
    //         'trang_thai_don_hang' => 'required|string',
    //         'trang_thai_thanh_toan' => 'required|string',
    //         'order_details' => 'required|array',
    //     ]);

    //     $order = Order::create([
    //         'ma_don_hang' => $request->ma_don_hang,
    //         'user_id' => $request->nguoi_dung_id,
    //         'phuong_thuc_thanh_toan_id' => $request->phuong_thuc_thanh_toan_id,
    //         'trang_thai_don_hang' => $request->trang_thai_don_hang,
    //         'trang_thai_thanh_toan' => $request->trang_thai_thanh_toan,
    //     ]);

    //     foreach ($request->order_details as $item) {
    //         $order->orderDetails()->create($item);
    //     }

    //     return response()->json(['message' => 'Tạo đơn hàng thành công'], 201);
    // }

    // Cập nhật trạng thái đơn hàng
public function update(Request $request, $id)
{
    $validated = $request->validate([
        'trang_thai_don_hang' => 'nullable|in:cho_xac_nhan,dang_chuan_bi,dang_van_chuyen,da_giao,da_huy,tra_hang',
        'trang_thai_thanh_toan' => 'nullable|in:cho_xu_ly,da_thanh_toan,that_bai,hoan_tien,da_huy',
    ]);

    $order = Order::findOrFail($id);

    // Luồng hợp lệ của trạng thái đơn hàng
    $orderStatusFlow = [
        'cho_xac_nhan' => ['dang_chuan_bi', 'da_huy'],
        'dang_chuan_bi' => ['dang_van_chuyen', 'da_huy'],
        'dang_van_chuyen' => ['da_giao', 'tra_hang'],
        'da_giao' => ['tra_hang'],
        'da_huy' => [],
        'tra_hang' => [],
    ];

    // Luồng hợp lệ của trạng thái thanh toán
    $paymentStatusFlow = [
        'cho_xu_ly' => ['da_thanh_toan', 'that_bai', 'da_huy'],
        'da_thanh_toan' => ['hoan_tien'],
        'that_bai' => [],
        'hoan_tien' => [],
        'da_huy' => [],
    ];

    // Kiểm tra trạng thái đơn hàng nếu có gửi
    if (isset($validated['trang_thai_don_hang'])) {
        $current = $order->trang_thai_don_hang;
        $next = $validated['trang_thai_don_hang'];

        if (!in_array($next, $orderStatusFlow[$current])) {
            return response()->json(['message' => "Không thể chuyển trạng thái đơn hàng từ '$current' sang '$next'."
            ], 400);
        }
    }

    // Kiểm tra trạng thái thanh toán nếu có gửi
    if (isset($validated['trang_thai_thanh_toan'])) {
        $current = $order->trang_thai_thanh_toan;
        $next = $validated['trang_thai_thanh_toan'];

        if (!in_array($next, $paymentStatusFlow[$current])) {
            return response()->json([
                'message' => "Không thể chuyển trạng thái thanh toán từ '$current' sang '$next'."
            ], 400);
        }
    }

    $order->update($validated);

    return response()->json([
        'message' => 'Cập nhật đơn hàng thành công',
        'order' => $order
    ]);
}



    // Xoá đơn hàng
    // public function destroy($id)
    // {
    //     $order = Order::findOrFail($id);
    //     $order->orderDetails()->delete();
    //     $order->delete();

    //     return response()->json(['message' => 'Xoá đơn hàng thành công']);
    // }
}