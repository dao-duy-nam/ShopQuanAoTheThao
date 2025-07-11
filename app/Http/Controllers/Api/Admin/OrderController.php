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
        'trang_thai_don_hang' => 'nullable|in:cho_xac_nhan,dang_chuan_bi,dang_van_chuyen,da_giao,da_huy,tra_hang',
        'trang_thai_thanh_toan' => 'nullable|in:cho_xu_ly,da_thanh_toan,that_bai,hoan_tien,da_huy',
        'dia_chi' => 'nullable|string|max:255',
    ]);

    $order = Order::with('user')->findOrFail($id);

    $orderStatusFlow = [
        'cho_xac_nhan' => ['dang_chuan_bi', 'da_huy'],
        'dang_chuan_bi' => ['dang_van_chuyen', 'da_huy'],
        'dang_van_chuyen' => ['da_giao', 'tra_hang'],
        'da_giao' => ['tra_hang'],
        'da_huy' => [],
        'tra_hang' => [],
    ];

    $paymentStatusFlow = [
        'cho_xu_ly' => ['da_thanh_toan', 'that_bai', 'da_huy'],
        'da_thanh_toan' => ['hoan_tien'],
        'that_bai' => [],
        'hoan_tien' => [],
        'da_huy' => [],
    ];

    $hasOrderStatusChanged = false;
    $hasPaymentStatusChanged = false;

    if (isset($validated['trang_thai_don_hang'])) {
        $currentOrderStatus = $order->trang_thai_don_hang;
        $nextOrderStatus = $validated['trang_thai_don_hang'];

        if (!in_array($nextOrderStatus, $orderStatusFlow[$currentOrderStatus])) {
            return response()->json([
                'message' => "Không thể chuyển trạng thái đơn hàng từ '$currentOrderStatus' sang '$nextOrderStatus'."
            ], 400);
        }

        $hasOrderStatusChanged = $currentOrderStatus !== $nextOrderStatus;

        // Gán tự động trạng thái thanh toán
        if ($nextOrderStatus === 'da_giao') {
            $validated['trang_thai_thanh_toan'] = 'da_thanh_toan';
        } elseif ($nextOrderStatus === 'da_huy') {
            $validated['trang_thai_thanh_toan'] = 'hoan_tien';
        } elseif ($nextOrderStatus === 'tra_hang') {
            $validated['trang_thai_thanh_toan'] = 'da_huy';
        }
    }

    if (isset($validated['trang_thai_thanh_toan'])) {
        $currentPaymentStatus = $order->trang_thai_thanh_toan;
        $nextPaymentStatus = $validated['trang_thai_thanh_toan'];

        if (!in_array($nextPaymentStatus, $paymentStatusFlow[$currentPaymentStatus])) {
            return response()->json([
                'message' => "Không thể chuyển trạng thái thanh toán từ '$currentPaymentStatus' sang '$nextPaymentStatus'."
            ], 400);
        }

        $hasPaymentStatusChanged = $currentPaymentStatus !== $nextPaymentStatus;
    }

    $order->update($validated);

    // === Gửi mail nếu có thay đổi trạng thái ===
    if ($hasOrderStatusChanged || $hasPaymentStatusChanged) {
        $message = "Trạng thái đơn hàng của bạn đã được cập nhật.";
        Mail::to($order->user->email)->send(new OrderStatusChangedMail($order, $message));

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Cập nhật trạng thái đơn hàng #' . $order->id,
            'status' => 'thông tin'
        ]);
    }

    return response()->json([
        'message' => 'Cập nhật đơn hàng thành công',
        'order' => $order
    ]);
}


}
