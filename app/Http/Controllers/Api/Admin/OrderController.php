<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Danh sách đơn hàng với tìm kiếm và phân trang
     */
    public function index(Request $request)
    {
        $query = Order::query();

        // Tìm kiếm nếu có
        if ($request->has('search')) {
            $search = $request->input('search');

            $query->where(function ($query) use ($search) {
                $query->where('ma_don_hang', 'like', "%$search%")
                      ->orWhere('user_id', 'like', "%$search%")
                      ->orWhere('trang_thai_don_hang', 'like', "%$search%")
                      ->orWhere('dia_chi', 'like', "%$search%");  // Cho phép search theo địa chỉ
            });
        }

        $orders = $query->paginate(10); // 10 đơn hàng mỗi trang

        return response()->json($orders);
    }

    /**
     * Chi tiết đơn hàng theo ID
     */
public function show($id)
{
    $order = Order::with([
        'orderDetail.product',    // Lấy toàn bộ cột của bảng san_phams
        'orderDetail.variant',    // (nếu muốn lấy luôn biến thể)
        'paymentMethod'           // Lấy phương thức thanh toán
    ])->findOrFail($id);

    return response()->json($order);
}

    /**
     * Cập nhật trạng thái đơn hàng, trạng thái thanh toán và địa chỉ
     */
public function update(Request $request, $id)
{
    $validated = $request->validate([
        'trang_thai_don_hang' => 'nullable|in:cho_xac_nhan,dang_chuan_bi,dang_van_chuyen,da_giao,yeu_cau_tra_hang,cho_xac_nhan_tra_hang,tra_hang_thanh_cong,yeu_cau_huy,cho_xac_nhan_huy,huy_thanh_cong',
        'dia_chi' => 'nullable|string|max:255',
    ]);

    $order = Order::findOrFail($id);

    // Luồng trạng thái đơn hàng
    $orderStatusFlow = [
        'cho_xac_nhan' => ['dang_chuan_bi', 'da_huy', 'yeu_cau_huy'],
        'dang_chuan_bi' => ['dang_van_chuyen', 'da_huy', 'yeu_cau_huy'],
        'dang_van_chuyen' => ['da_giao'],
        'da_giao' => ['yeu_cau_tra_hang', 'yeu_cau_huy'],
        'yeu_cau_tra_hang' => ['cho_xac_nhan_tra_hang'],
        'cho_xac_nhan_tra_hang' => ['tra_hang_thanh_cong'],
        'tra_hang_thanh_cong' => [],
        
        'yeu_cau_huy' => ['cho_xac_nhan_huy'],
        'cho_xac_nhan_huy' => ['da_huy'],

        'da_huy' => [],
    ];

    $currentOrderStatus = $order->trang_thai_don_hang;

    if (isset($validated['trang_thai_don_hang'])) {
        $nextOrderStatus = $validated['trang_thai_don_hang'];

        // Luồng trả hàng
        if ($currentOrderStatus === 'yeu_cau_tra_hang' && $nextOrderStatus !== 'cho_xac_nhan_tra_hang') {
            return response()->json([
                'message' => "Đơn hàng đang yêu cầu trả hàng, chỉ được xác nhận sang 'cho_xac_nhan_tra_hang'."
            ], 400);
        }

        if ($currentOrderStatus === 'cho_xac_nhan_tra_hang' && $nextOrderStatus !== 'tra_hang_thanh_cong') {
            return response()->json([
                'message' => "Đơn hàng đang chờ xác nhận trả hàng, chỉ được xác nhận sang 'tra_hang_thanh_cong'."
            ], 400);
        }

        // Luồng hủy đơn
        if ($currentOrderStatus === 'yeu_cau_huy' && $nextOrderStatus !== 'cho_xac_nhan_huy') {
            return response()->json([
                'message' => "Đơn hàng đang yêu cầu hủy, chỉ được xác nhận sang 'cho_xac_nhan_huy'."
            ], 400);
        }

        if ($currentOrderStatus === 'cho_xac_nhan_huy' && $nextOrderStatus !== 'da_huy') {
            return response()->json([
                'message' => "Đơn hàng đang chờ xác nhận hủy, chỉ được xác nhận sang 'huy_thanh_cong'."
            ], 400);
        }

        if (!in_array($nextOrderStatus, $orderStatusFlow[$currentOrderStatus])) {
            return response()->json([
                'message' => "Không thể chuyển trạng thái đơn hàng từ '$currentOrderStatus' sang '$nextOrderStatus'."
            ], 400);
        }

        // Auto set trạng thái thanh toán theo trạng thái đơn hàng
        if ($nextOrderStatus === 'cho_xac_nhan') {
            $validated['trang_thai_thanh_toan'] = 'cho_xu_ly';
        } elseif ($nextOrderStatus === 'da_giao') {
            $validated['trang_thai_thanh_toan'] = 'da_thanh_toan';
        } elseif ($nextOrderStatus === 'yeu_cau_tra_hang') {
            $validated['trang_thai_thanh_toan'] = 'cho_hoan_tien';
        } elseif ($nextOrderStatus === 'tra_hang_thanh_cong') {
            $validated['trang_thai_thanh_toan'] = 'hoan_tien';
        } elseif ($nextOrderStatus === 'yeu_cau_huy') {
            $validated['trang_thai_thanh_toan'] = 'da_huy';
        } elseif ($nextOrderStatus === 'cho_xac_nhan_huy') {
            $validated['trang_thai_thanh_toan'] = 'da_huy';
        } elseif ($nextOrderStatus === 'da_huy') {
            $validated['trang_thai_thanh_toan'] = 'da_huy';
        }
    }

    $order->update($validated);

    return response()->json([
        'message' => 'Cập nhật đơn hàng thành công',
        'order' => $order
    ]);
}



}
