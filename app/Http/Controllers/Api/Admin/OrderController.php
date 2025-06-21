<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController
{
    // Danh sách đơn hàng
public function index(Request $request)
    {
        // Lấy tất cả các đơn hàng với phân trang
        $query = Order::query();

        // Kiểm tra nếu có giá trị tìm kiếm trong request
        if ($request->has('search')) {
            $search = $request->input('search');
            
            // Tìm kiếm theo các cột: ma_don_hang, user_id, trang_thai_don_hang
            $query->where(function($query) use ($search) {
                $query->where('ma_don_hang', 'like', "%$search%")
                      ->orWhere('user_id', 'like', "%$search%")
                      ->orWhere('trang_thai_don_hang', 'like', "%$search%");
            });
        }

        // Phân trang kết quả
        $orders = $query->paginate(10);  // 10 đơn hàng mỗi trang

        // Trả về kết quả tìm kiếm và phân trang dưới dạng JSON
        return response()->json($orders);
    }

    // Chi tiết đơn hàng
    public function show($id)
    {
        $order = Order::with(['orderDetail', 'paymentMethod'])->findOrFail($id);
        return response()->json($order);
    }
    
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




}