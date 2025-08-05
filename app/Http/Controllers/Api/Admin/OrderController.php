<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Order;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Mail\OrderStatusChangedMail;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        $query = Order::query();

        if ($request->has('search')) {
            $search = $request->input('search');

            $query->where(function ($query) use ($search) {
                $query->where('ma_don_hang', 'like', "%$search%")
                    ->orWhere('user_id', 'like', "%$search%")
                    ->orWhere('trang_thai_don_hang', 'like', "%$search%")
                    ->orWhere('dia_chi', 'like', "%$search%");  
            });
        }
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai_don_hang', $request->trang_thai);
        }

        if ($request->filled('date')) {
            $date = $request->date;
            $query->whereDate('created_at', $date);
        }

        $orders = $query->orderBy('created_at','desc')->paginate(10);

        return response()->json($orders);
    }


    public function show($id)
    {
        $order = Order::with([
            'orderDetail.product',    
            'orderDetail.variant',    
            'paymentMethod'           
        ])->findOrFail($id);

        return response()->json($order);
    }


public function update(Request $request, $id)
{
    $validated = $request->validate([
        'trang_thai_don_hang' => 'nullable|in:cho_xac_nhan,dang_chuan_bi,dang_van_chuyen,da_giao,yeu_cau_tra_hang,cho_xac_nhan_tra_hang,tra_hang_thanh_cong,yeu_cau_huy_hang,tu_choi_tra_hang',
        'dia_chi' => 'nullable|string|max:255',
        'ly_do_tu_choi_tra_hang' => 'nullable|string|max:255',
    ]);

    $order = Order::findOrFail($id);
    $currentStatus = $order->trang_thai_don_hang;

    if (isset($validated['trang_thai_don_hang'])) {
        $nextStatus = $validated['trang_thai_don_hang'];

        $userOnlyStatuses = ['yeu_cau_tra_hang', 'yeu_cau_huy_hang'];
        if (in_array($nextStatus, $userOnlyStatuses)) {
            return response()->json([
                'message' => "Trạng thái '$nextStatus' chỉ được cập nhật bởi người dùng."
            ], 403);
        }

        if ($currentStatus === 'yeu_cau_tra_hang' && !in_array($nextStatus, ['cho_xac_nhan_tra_hang', 'tu_choi_tra_hang'])) {
            return response()->json([
                'message' => "Đơn hàng đang yêu cầu trả hàng, chỉ được xác nhận sang 'cho_xac_nhan_tra_hang' hoặc 'tu_choi_tra_hang'."
            ], 400);
        }

        if ($currentStatus === 'cho_xac_nhan_tra_hang' && $nextStatus !== 'tra_hang_thanh_cong') {
            return response()->json([
                'message' => "Đơn hàng đang chờ xác nhận trả hàng, chỉ được xác nhận sang 'tra_hang_thanh_cong'."
            ], 400);
        }

        $orderStatusFlow = [
            'cho_xac_nhan' => ['dang_chuan_bi'],
            'dang_chuan_bi' => ['dang_van_chuyen'],
            'dang_van_chuyen' => ['da_giao'],
            'da_giao' => [],
            'yeu_cau_tra_hang' => ['cho_xac_nhan_tra_hang', 'tu_choi_tra_hang'],
            'cho_xac_nhan_tra_hang' => ['tra_hang_thanh_cong'],
            'tra_hang_thanh_cong' => [],
            'yeu_cau_huy_hang' => [], 
            'tu_choi_tra_hang' => [],
        ];

        if (!in_array($nextStatus, $orderStatusFlow[$currentStatus] ?? [])) {
            return response()->json([
                'message' => "Không thể chuyển trạng thái từ '$currentStatus' sang '$nextStatus'."
            ], 400);
        }

        if ($nextStatus === 'tu_choi_tra_hang' && empty($validated['ly_do_tu_choi_tra_hang'])) {
            return response()->json([
                'message' => 'Vui lòng nhập lý do từ chối trả hàng.'
            ], 422);
        }

        if ($nextStatus === 'cho_xac_nhan') {
            $validated['trang_thai_thanh_toan'] = 'cho_xu_ly';
        } 
         else if ($nextStatus === 'da_giao') {
            $validated['trang_thai_thanh_toan'] = 'da_thanh_toan';
        }else if ($nextStatus === 'cho_xac_nhan_tra_hang') {
            $validated['trang_thai_thanh_toan'] = 'cho_hoan_tien';
        } else if ($nextStatus === 'tra_hang_thanh_cong') {
            $validated['trang_thai_thanh_toan'] = 'hoan_tien';
        }
    }

    $order->update($validated);

    if (isset($nextStatus)) {
        $message = "Đơn hàng đã được cập nhật trạng thái: $nextStatus.";

        if ($nextStatus === 'tu_choi_tra_hang') {
            $message .= " Lý do: " . $validated['ly_do_tu_choi_tra_hang'];
        }

        Mail::to($order->email_nguoi_dat)->queue(new OrderStatusChangedMail($order, $message));
    }

    return response()->json([
        'message' => 'Cập nhật đơn hàng thành công',
        'order' => $order
    ]);
}

public function cancel(Request $request, $id, WalletService $walletService)
{
    $validated = $request->validate([
        'ly_do_huy' => 'required|string|max:255',
    ]);

    $order = Order::findOrFail($id);
    $currentStatus = $order->trang_thai_don_hang;

    if (!in_array($currentStatus, ['cho_xac_nhan', 'dang_chuan_bi'])) {
        return response()->json([
            'message' => "Không thể hủy đơn hàng ở trạng thái '$currentStatus'."
        ], 400);
    }

    DB::beginTransaction();

    try {
        $pttt = (int) $order->phuong_thuc_thanh_toan_id;

        if (in_array($pttt, [2, 3])) {
            $trangThaiThanhToan = 'cho_hoan_tien';
        } else if ($pttt === 1) {
            $trangThaiThanhToan = 'da_huy';
        } else {
            $trangThaiThanhToan = 'da_huy';
        }

        $order->update([
            'trang_thai_don_hang' => 'da_huy',
            'ly_do_huy' => $validated['ly_do_huy'],
            'trang_thai_thanh_toan' => $trangThaiThanhToan,
        ]);

        $onlineMethods = ['vnpay', 'zalopay'];
        if (
            in_array(optional($order->paymentMethod)->code, $onlineMethods) &&
            $order->trang_thai_thanh_toan === 'da_thanh_toan' &&
            !$order->refund_done
        ) {
            $walletService->refund($order->user, $order->id, $order->so_tien_thanh_toan);
            $order->update(['refund_done' => true]);
        }

        $message = "Đơn hàng đã bị hủy. Lý do: " . $validated['ly_do_huy'];
        Mail::to($order->email_nguoi_dat)->send(new OrderStatusChangedMail($order, $message));

        DB::commit();
        $order->refresh();

        return response()->json([
            'message' => 'Đơn hàng đã được hủy thành công.',
            'order' => $order
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'Lỗi khi hủy đơn hàng: ' . $e->getMessage()], 500);
    }
}


}