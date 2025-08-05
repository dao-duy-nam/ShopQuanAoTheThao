<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class WalletTransactionController extends Controller
{
    private $validTransactionTypes = [
        'deposit',  // Nạp tiền
        'withdraw', // Rút tiền
        'payment',  // Thanh toán đơn hàng
        'refund'    // Hoàn tiền
    ];

    public function index(Request $request)
    {
        $query = WalletTransaction::with(['user:id,name,email,so_dien_thoai','order']);


        if ($request->filled('keyword')) {
            $keyword = $request->keyword;

            $query->whereHas('user', function ($userQuery) use ($keyword) {
                $userQuery->where('name', 'like', "%$keyword%")
                    ->orWhere('email', 'like', "%$keyword%")
                    ->orWhere('so_dien_thoai', 'like', "%$keyword%");
            });
        }

        if ($request->filled('type')) {
            // Kiểm tra tính hợp lệ của type
            if (!in_array($request->type, $this->validTransactionTypes)) {
                return response()->json([
                    'message' => 'Loại giao dịch không hợp lệ. Các loại hợp lệ: ' . implode(', ', $this->validTransactionTypes)
                ], 400);
            }
            $query->where('type', $request->type);
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->latest()->paginate(10);

        return response()->json($transactions);
    }


    public function show($id)
    {
        $transaction = WalletTransaction::with(['user:id,name,email,so_dien_thoai'])->findOrFail($id);
        return response()->json($transaction);
    }


    public function updateStatus(Request $request, $id)
{
    $transaction = WalletTransaction::findOrFail($id);

    $newStatus = $request->input('status');
    $rejectionReason = $request->input('rejection_reason');

    $currentStatus = $transaction->status;

    
    $allowedTransitions = [
        'pending' => ['success', 'rejected'],
        'success' => ['rejected'],
        'rejected' => [],
    ];

    
    if (!in_array($newStatus, $allowedTransitions[$currentStatus])) {
        return response()->json([
            'message' => "Không thể chuyển từ trạng thái '$currentStatus' sang '$newStatus'."
        ], 400);
    }

   
    if ($newStatus === 'rejected' && empty($rejectionReason)) {
        return response()->json([
            'message' => "Bạn phải nhập lý do từ chối khi chuyển trạng thái sang 'rejected'."
        ], 422);
    }

    
    $transaction->status = $newStatus;

    
    if ($newStatus === 'rejected') {
        $transaction->rejection_reason = $rejectionReason;
    }

    $transaction->save();

    return response()->json([
        'message' => "Cập nhật trạng thái thành công",
        'data' => $transaction
    ]);
}

}
