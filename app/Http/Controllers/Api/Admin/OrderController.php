<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrderStatusChangedMail;
use App\Models\ActivityLog;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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


public function update(Request $request, $id)
{
    $validated = $request->validate([
        'trang_thai_don_hang' => 'nullable|in:cho_xac_nhan,dang_chuan_bi,dang_van_chuyen,da_giao,yeu_cau_tra_hang,cho_xac_nhan_tra_hang,tra_hang_thanh_cong,yeu_cau_huy_hang,da_huy',
        'dia_chi' => 'nullable|string|max:255',
    ]);

    $order = Order::findOrFail($id);
    $currentStatus = $order->trang_thai_don_hang;

    if (isset($validated['trang_thai_don_hang'])) {
        $nextStatus = $validated['trang_thai_don_hang'];

        $userOnlyStatuses = ['da_giao', 'yeu_cau_tra_hang', 'yeu_cau_huy_hang'];
        if (in_array($nextStatus, $userOnlyStatuses)) {
            return response()->json([
                'message' => "Trạng thái '$nextStatus' chỉ được cập nhật bởi người dùng."
            ], 403);
        }

        if ($currentStatus === 'yeu_cau_tra_hang' && $nextStatus !== 'cho_xac_nhan_tra_hang') {
            return response()->json([
                'message' => "Đơn hàng đang yêu cầu trả hàng, chỉ được xác nhận sang 'cho_xac_nhan_tra_hang'."
            ], 400);
        }

        if ($currentStatus === 'cho_xac_nhan_tra_hang' && $nextStatus !== 'tra_hang_thanh_cong') {
            return response()->json([
                'message' => "Đơn hàng đang chờ xác nhận trả hàng, chỉ được xác nhận sang 'tra_hang_thanh_cong'."
            ], 400);
        }

        if ($currentStatus === 'yeu_cau_huy_hang' && $nextStatus !== 'da_huy') {
            return response()->json([
                'message' => "Đơn hàng đang yêu cầu hủy, chỉ được xác nhận sang 'da_huy'."
            ], 400);
        }

        $orderStatusFlow = [
            'cho_xac_nhan' => ['dang_chuan_bi', 'da_huy'],
            'dang_chuan_bi' => ['dang_van_chuyen', 'da_huy'],
            'dang_van_chuyen' => [],
            'da_giao' => [],
            'yeu_cau_tra_hang' => ['cho_xac_nhan_tra_hang'],
            'cho_xac_nhan_tra_hang' => ['tra_hang_thanh_cong'],
            'tra_hang_thanh_cong' => [],
            'yeu_cau_huy_hang' => ['da_huy'],
            'da_huy' => [],
        ];

        if (!in_array($nextStatus, $orderStatusFlow[$currentStatus] ?? [])) {
            return response()->json([
                'message' => "Không thể chuyển trạng thái từ '$currentStatus' sang '$nextStatus'."
            ], 400);
        }

        if ($nextStatus === 'cho_xac_nhan') {
            $validated['trang_thai_thanh_toan'] = 'cho_xu_ly';
        } else if ($nextStatus === 'cho_xac_nhan_tra_hang') {
            $validated['trang_thai_thanh_toan'] = 'cho_hoan_tien';
        } else if ($nextStatus === 'tra_hang_thanh_cong') {
            $validated['trang_thai_thanh_toan'] = 'hoan_tien';
        } else if ($nextStatus === 'da_huy') {
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