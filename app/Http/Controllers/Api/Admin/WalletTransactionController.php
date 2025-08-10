<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Wallet;
use Illuminate\Http\Request;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class WalletTransactionController extends Controller
{
    private $validTransactionTypes = [
        'deposit',
        'withdraw',
        'payment',
        'refund'
    ];

    public function index(Request $request)
    {
        $query = WalletTransaction::with(['user:id,name,email,so_dien_thoai', 'order']);


        if ($request->filled('keyword')) {
            $keyword = $request->keyword;

            $query->whereHas('user', function ($userQuery) use ($keyword) {
                $userQuery->where('name', 'like', "%$keyword%")
                    ->orWhere('email', 'like', "%$keyword%")
                    ->orWhere('so_dien_thoai', 'like', "%$keyword%");
            });
        }

        if ($request->filled('type')) {

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

        if ($transaction->type !== 'withdraw') {
            return response()->json([
                'message' => 'Chỉ có giao dịch rút tiền mới được phép cập nhật trạng thái.'
            ], 403);
        }

        $newStatus = $request->input('status');
        $rejectionReason = $request->input('rejection_reason');
        $currentStatus = $transaction->status;

        $allowedTransitions = [
            'pending' => ['success', 'rejected'],
            'success' => [],
            'rejected' => [],
        ];

        if (!in_array($newStatus, $allowedTransitions[$currentStatus])) {
            return response()->json([
                'message' => "Không thể chuyển từ trạng thái '$currentStatus' sang '$newStatus'."
            ], 400);
        }

        if ($newStatus === 'rejected' && empty($rejectionReason)) {
            return response()->json([
                'message' => "Bạn phải nhập lý do từ chối khi chuyển trạng thái sang thất bại."
            ], 422);
        }

        DB::beginTransaction();
        try {
            $wallet = Wallet::where('user_id', $transaction->user_id)->first();
            if (!$wallet) {
                DB::rollBack();
                return response()->json(['message' => 'Không tìm thấy ví người dùng.'], 404);
            }

            if ($newStatus === 'success') {
                
                if (!$request->hasFile('transfer_image')) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Bạn phải tải lên ảnh minh chứng khi duyệt thành công.'
                    ], 422);
                }

                $path = $request->file('transfer_image')->store('transfers', 'public');
                $transaction->transfer_image = $path;

                
                if ($wallet->frozen_balance < $transaction->amount) {
                    DB::rollBack();
                    return response()->json(['message' => 'Số tiền đóng băng không đủ.'], 400);
                }
                $wallet->frozen_balance -= $transaction->amount;
                $wallet->save();
            }

            if ($newStatus === 'rejected') {
                $transaction->rejection_reason = $rejectionReason;

                
                if ($wallet->frozen_balance < $transaction->amount) {
                    DB::rollBack();
                    return response()->json(['message' => 'Số tiền đóng băng không đủ để hoàn.'], 400);
                }
                $wallet->frozen_balance -= $transaction->amount;
                $wallet->balance += $transaction->amount;
                $wallet->save();
            }

            $transaction->status = $newStatus;
            $transaction->save();

            DB::commit();

            return response()->json([
                'message' => "Cập nhật trạng thái thành công",
                'data' => $transaction
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }
}
